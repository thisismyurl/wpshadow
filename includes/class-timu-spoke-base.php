<?php
/**
 * TIMU Spoke Base Class - Reusable framework for all spoke plugins
 *
 * The central orchestration layer for spoke plugins (Image, Media, Formats, etc).
 * Moved from Image plugin's embedded core to enable DRY principle across suite.
 *
 * @package     wp_support_SUPPORT
 * @version     1.2601.073000
 * @since       1.0.0
 * @author      Senior Architect
 */

declare(strict_types=1);

namespace TIMU\Core\Spoke;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TIMU_Spoke_Base Class
 *
 * Base class for all spoke plugins extending Core support.
 * Provides admin UI, settings management, asset enqueuing, and component loading.
 */
abstract class TIMU_Spoke_Base {

	/** @var array Shared instances of all active plugins. */
	public static array $instances = array();

	public string $plugin_slug;
	public string $plugin_url;
	public string $options_group;
	public string $plugin_icon;
	public string $menu_parent;
	public string $license_message        = '';
	public array $settings_blueprint      = array();
	private ?string $data_prefix_cache    = null;
	private ?array $options_cache         = null;
	private ?string $plugin_version_cache = null;

	/** @var \WP_Filesystem_Base|null The filesystem object. */
	public $fs = null;

	/** @var \TIMU\Core\Spoke\TIMU_Admin_v1|null Administrative UI handler. */
	public $admin;

	/** @var \TIMU\Core\Spoke\TIMU_Ajax_v1|null AJAX endpoint handler. */
	public $ajax;

	/** @var \TIMU\Core\Spoke\TIMU_Processor_v1|null Image processing logic. */
	public $processor;

	/** @var \TIMU\Core\Spoke\TIMU_Vault_v1|null Backup and recovery handler. */
	public $vault;

	/**
	 * Constructor.
	 */
	public function __construct( string $slug, string $url, string $group, string $icon = '', string $parent = 'thisismyurl-support' ) {
		$this->plugin_slug   = $slug;
		$this->plugin_url    = \trailingslashit( $url );
		$this->options_group = $group;
		$this->plugin_icon   = $icon;
		$this->menu_parent   = $parent;

		self::$instances[ $this->plugin_slug ] = $this;

		$this->load_components();

		\add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_core_assets' ) );
		\add_filter( "plugin_action_links_{$this->plugin_slug}/{$this->plugin_slug}.php", array( $this, 'add_plugin_action_links' ) );
		\add_action( 'admin_init', array( $this, 'handle_core_activation_redirect' ) );
		\add_filter( 'the_content', array( $this, 'filter_content_images' ), 99 );

		if ( $this->admin ) {
			\add_action( 'timu_sidebar_under_banner', array( $this->admin, 'render_default_sidebar_actions' ) );
		}

		\add_filter( 'attachment_fields_to_edit', array( $this, 'add_media_sidebar_actions' ), 10, 2 );
	}

	/**
	 * Loads spoke components dynamically.
	 */
	private function load_components(): void {
		$dir   = \plugin_dir_path( __FILE__ ) . 'spoke/';
		$files = array(
			'vault'     => $dir . 'class-timu-vault-v1.php',
			'admin'     => $dir . 'class-timu-admin-v1.php',
			'ajax'      => $dir . 'class-timu-ajax-v1.php',
			'processor' => $dir . 'class-timu-processor-v1.php',
		);

		foreach ( $files as $file ) {
			if ( \file_exists( $file ) ) {
				require_once $file;
			}
		}

		if ( \class_exists( __NAMESPACE__ . '\TIMU_Vault_v1' ) ) {
			$this->vault = new TIMU_Vault_v1( $this );
		}

		if ( \is_admin() || \wp_doing_ajax() ) {
			if ( \class_exists( __NAMESPACE__ . '\TIMU_Processor_v1' ) ) {
				$this->processor = new TIMU_Processor_v1( $this );
			}
			if ( \class_exists( __NAMESPACE__ . '\TIMU_Ajax_v1' ) ) {
				$this->ajax = new TIMU_Ajax_v1( $this );
			}
			if ( \class_exists( __NAMESPACE__ . '\TIMU_Admin_v1' ) ) {
				$this->admin = new TIMU_Admin_v1( $this );
			}
		}
	}

	/**
	 * Placebo License Check.
	 */
	public function is_licensed(): mixed {
		return null;
	}

	/**
	 * Shared image conversion logic.
	 */
	public function run_conversion_logic( int $id ): array {
		$prefix      = $this->get_data_prefix();
		$savings_key = "_{$prefix}_savings";
		$file_path   = \get_attached_file( $id );

		if ( ! $file_path || ! \file_exists( (string) $file_path ) ) {
			return array(
				'success' => false,
				'message' => 'File not found.',
			);
		}

		$old_size   = (int) \filesize( (string) $file_path );
		$vault_path = $this->vault->get_vault_path( (string) $file_path );

		if ( ! $this->vault->move_to_vault( (string) $file_path, $vault_path ) ) {
			return array(
				'success' => false,
				'message' => 'Vaulting failed.',
			);
		}

		$quality        = (int) $this->get_plugin_option( 'quality', 80 );
		$default_target = ( \str_contains( $this->plugin_slug, 'avif' ) ) ? 'avif' : 'webp';
		$target         = (string) $this->get_plugin_option( 'target_format', $default_target );

		$result = $this->processor->process_image_conversion(
			array(
				'file' => $vault_path,
				'url'  => \wp_get_attachment_url( $id ),
			),
			$target,
			$quality
		);

		if ( isset( $result['file'] ) && \file_exists( (string) $result['file'] ) ) {
			$this->processor->update_attachment_references( $id, (string) $result['file'], $target );
			$saved_amount = $old_size - (int) \filesize( (string) $result['file'] );

			\update_post_meta( $id, "_{$prefix}_original_path", $vault_path );
			\update_post_meta( $id, $savings_key, $saved_amount );

			$this->increment_total_savings_tracker( $saved_amount );
			$this->invalidate_bulk_stats();

			return array(
				'success' => true,
				'message' => 'Optimized!',
			);
		}

		$this->vault->recover_from_vault( $vault_path, (string) $file_path );
		return array(
			'success' => false,
			'message' => 'Failed.',
		);
	}

	/**
	 * Dynamic prefix resolver for labeling and metadata (cached).
	 */
	public function get_data_prefix(): string {
		if ( null !== $this->data_prefix_cache ) {
			return $this->data_prefix_cache;
		}
		$this->data_prefix_cache = match ( true ) {
			\str_contains( $this->plugin_slug, 'webp' )  => 'webp',
			\str_contains( $this->plugin_slug, 'avif' )  => 'avif',
			\str_contains( $this->plugin_slug, 'bmp' )   => 'bmp',
			\str_contains( $this->plugin_slug, 'heic' )  => 'heic',
			\str_contains( $this->plugin_slug, 'raw' )   => 'raw',
			\str_contains( $this->plugin_slug, 'svg' )   => 'svg',
			\str_contains( $this->plugin_slug, 'tiff' )  => 'tiff',
			\str_contains( $this->plugin_slug, 'link' )  => 'link',
			\str_contains( $this->plugin_slug, 'media' ) => 'media',
			default                                     => 'timu'
		};
		return $this->data_prefix_cache;
	}

	public function get_plugin_option( string $key = '', mixed $default = '' ): mixed {
		// Always fetch fresh to ensure we get the latest saved values
		$options = (array) \get_option( $this->plugin_slug . '_options', array() );
		return empty( $key ) ? $options : ( $options[ $key ] ?? $default );
	}

	/**
	 * Alias for get_plugin_option() for cleaner code.
	 */
	public function get_option( string $key = '', mixed $default = '' ): mixed {
		return $this->get_plugin_option( $key, $default );
	}

	public function clear_option_cache(): void {
		$this->options_cache = null;
	}

	public function init_fs(): \WP_Filesystem_Base {
		if ( null === $this->fs ) {
			global $wp_filesystem;
			if ( empty( $wp_filesystem ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				\WP_Filesystem();
			}
			$this->fs = $wp_filesystem;
		}
		return $this->fs;
	}

	public function handle_core_activation_redirect(): void {
		if ( \get_transient( "{$this->plugin_slug}_activation_redirect" ) ) {
			\delete_transient( "{$this->plugin_slug}_activation_redirect" );
			if ( ! \is_network_admin() && ! isset( $_GET['activate-multi'] ) ) {
				\wp_safe_redirect( \admin_url( 'admin.php?page=thisismyurl-support&tab=' . $this->plugin_slug ) );
				exit;
			}
		}
	}

	public function enqueue_core_assets( string $hook ): void {
		// Prevent any theme/plugin from removing version strings
		\remove_all_filters( 'script_loader_src' );
		\remove_all_filters( 'style_loader_src' );

		$version = $this->get_plugin_version();
		if ( '' === $version ) {
			$version = (string) \time();
		}

		// Compute cache-busting versions based on file mtimes (fallback to plugin version)
		$base_dir = \plugin_dir_path( ( new \ReflectionClass( $this ) )->getFileName() );
		$css_path = $base_dir . 'core/assets/css/shared-admin.css';
		$js_path  = $base_dir . 'core/assets/js/shared-admin.js';
		$bulk_js  = $base_dir . 'core/assets/js/shared-bulk.js';

		$css_ver  = \file_exists( $css_path ) ? ( $version . '-' . (string) \filemtime( $css_path ) ) : $version;
		$js_ver   = \file_exists( $js_path ) ? ( $version . '-' . (string) \filemtime( $js_path ) ) : $version;
		$bulk_ver = \file_exists( $bulk_js ) ? ( $version . '-' . (string) \filemtime( $bulk_js ) ) : $version;

		$css_url  = $this->plugin_url . 'core/assets/css/shared-admin.css';
		$js_url   = $this->plugin_url . 'core/assets/js/shared-admin.js';
		$bulk_url = $this->plugin_url . 'core/assets/js/shared-bulk.js';

		// Only enqueue files that actually exist
		if ( \file_exists( $css_path ) ) {
			\wp_enqueue_style( 'timu-core-css', $css_url, array(), $css_ver );
		}
		
		if ( \file_exists( $js_path ) ) {
			\wp_enqueue_script( 'timu-core-ui', $js_url, array( 'jquery' ), $js_ver, true );
		}

		$current_page = isset( $_GET['page'] ) ? \sanitize_key( $_GET['page'] ) : '';
		if ( $current_page === $this->plugin_slug || $current_page === 'thisismyurl-support' ) {
			if ( \file_exists( $bulk_js ) ) {
				\wp_enqueue_script( 'timu-core-bulk', $bulk_url, array( 'jquery', 'timu-core-ui' ), $bulk_ver, true );
			}
			\wp_enqueue_style( 'wp-color-picker' );
			\wp_enqueue_script( 'wp-color-picker' );
			\wp_enqueue_media();
		}

		\wp_localize_script(
			'timu-core-ui',
			'wp_support_vars',
			array(
				'ajax_url' => \admin_url( 'admin-ajax.php' ),
				'nonce'    => \wp_create_nonce( 'timu_install_nonce' ),
				'slug'     => $this->plugin_slug,
			)
		);
	}

	/**
	 * Resolve the plugin version (cached), used for asset versioning.
	 */
	protected function get_plugin_version(): string {
		if ( null !== $this->plugin_version_cache ) {
			return $this->plugin_version_cache;
		}
		$default   = (string) \date( 'U' );
		$main_file = WP_PLUGIN_DIR . '/' . $this->plugin_slug . '/' . $this->plugin_slug . '.php';
		if ( \file_exists( $main_file ) ) {
			$headers                    = \get_file_data( $main_file, array( 'Version' => 'Version' ) );
			$ver                        = isset( $headers['Version'] ) && is_string( $headers['Version'] ) && $headers['Version'] !== '' ? $headers['Version'] : $default;
			$this->plugin_version_cache = (string) $ver;
		} else {
			$this->plugin_version_cache = $default;
		}
		return $this->plugin_version_cache;
	}

	public function add_plugin_action_links( array $links ): array {
		$settings_url = \admin_url( 'admin.php?page=thisismyurl-support&tab=' . $this->plugin_slug );
		$register_url = 'https://thisismyurl.com/' . $this->plugin_slug . '/#register?source=plugin-list';
		$links[]      = '<a href="' . \esc_url( $settings_url ) . '">Settings</a>';
		$links[]      = '<a href="' . \esc_url( $register_url ) . '" target="_blank">Register</a>';
		return $links;
	}

	public function filter_content_images( string $content ): string {
		if ( \is_admin() || empty( $content ) ) {
			return $content;
		}
		try {
			$prefix     = $this->get_data_prefix();
			$target_ext = (string) $this->get_plugin_option( 'target_format', ( 'avif' === $prefix ) ? 'avif' : 'webp' );
			$pattern    = '/(href|src|srcset)=["\']([^"\']+\.(jpe?g|png))["\'](?i)';

			return (string) \preg_replace_callback(
				$pattern,
				function ( array $m ) use ( $prefix, $target_ext ) {
					$url       = $m[2];
					$cache_key = 'url_to_id_' . \md5( (string) $url );
					$id        = \wp_cache_get( $cache_key, 'timu_url_lookups' );
					if ( false === $id ) {
						$id = (int) \attachment_url_to_postid( (string) $url );
						\wp_cache_set( $cache_key, $id, 'timu_url_lookups', DAY_IN_SECONDS );
					}
					if ( $id > 0 && \get_post_meta( $id, "_{$prefix}_savings", true ) ) {
						return $m[1] . '="' . \preg_replace( '/\.(jpe?g|png)$/i', '.' . $target_ext, (string) $url ) . '"';
					}
					return $m[0];
				},
				$content
			);
		} catch ( \Exception $e ) {
			\error_log( 'TIMU Content Filter Error: ' . $e->getMessage() );
			return $content;
		}
	}

	public function get_bulk_stats(): array {
		$cache_key = "{$this->plugin_slug}_bulk_stats";
		$cached    = \wp_cache_get( $cache_key, 'wp_support' );
		if ( false !== $cached ) {
			return (array) $cached;
		}

		$prefix      = $this->get_data_prefix();
		$unprocessed = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => array( 'image/jpeg', 'image/png' ),
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'     => "_{$prefix}_savings",
						'compare' => 'NOT EXISTS',
					),
				),
				'fields'         => 'ids',
			)
		);
		$processed   = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'meta_query'     => array(
					array(
						'key'     => "_{$prefix}_savings",
						'compare' => 'EXISTS',
					),
				),
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		$stats = array(
			'unprocessed' => (int) $unprocessed->found_posts,
			'processed'   => (int) $processed->found_posts,
		);
		\wp_cache_set( $cache_key, $stats, 'wp_support', HOUR_IN_SECONDS );
		return $stats;
	}

	public function invalidate_bulk_stats(): void {
		\wp_cache_delete( "{$this->plugin_slug}_bulk_stats", 'wp_support' );
	}

	private function increment_total_savings_tracker( int $amount ): void {
		$option_name = "{$this->plugin_slug}_total_savings_count";
		$current     = (int) \get_option( $option_name, 0 );
		\update_option( $option_name, $current + $amount, false );
	}

	public function sanitize_core_options( mixed $input ): array {
		if ( ! \is_array( $input ) ) {
			$input = array();
		}
		\delete_transient( "{$this->plugin_slug}_license_check" );
		$this->invalidate_bulk_stats();
		$this->options_cache = null;
		$input['enabled']    = isset( $input['enabled'] ) ? 1 : 0;
		if ( isset( $input['license_key'] ) ) {
			$input['license_key'] = \sanitize_text_field( $input['license_key'] );
		}
		return $input;
	}

	public function add_media_sidebar_actions( array $form_fields, \WP_Post $post ): array {
		// Return form fields as-is. Subclasses can override this method to add custom fields.
		return $form_fields;
	}

	/**
	 * Common method: Handle legacy redirects to unified settings page
	 */
	public function handle_legacy_redirects(): void {
		if ( ! \is_admin() || ! \current_user_can( 'manage_options' ) ) {
			return;
		}
		$page = isset( $_GET['page'] ) ? \sanitize_key( $_GET['page'] ) : '';
		if ( $page === $this->plugin_slug ) {
			$current_screen = \get_current_screen();
			if ( ! $current_screen || 'toplevel_page_thisismyurl-support' !== $current_screen->id ) {
				\wp_safe_redirect( \admin_url( 'admin.php?page=thisismyurl-support&tab=' . $this->plugin_slug ) );
				exit;
			}
		}
	}

	/**
	 * Common method: Setup plugin with base blueprint
	 */
	public function setup_plugin_base(): void {
		$this->is_licensed();
		$blueprint = $this->get_base_blueprint();
		$this->init_settings_generator( $blueprint );
	}

	/**
	 * Common method: Activate plugin with default options
	 */
	protected function activate_plugin_defaults(): void {
		$option_name = "{$this->plugin_slug}_options";
		if ( false === \get_option( $option_name ) ) {
			$defaults = array(
				'enabled'       => 1,
				'target_format' => $this->get_default_format(),
				'quality'       => 60,
			);
			\add_option( $option_name, $defaults, '', 'no' );
		}
	}

	/**
	 * Common method: Add admin submenu for hub plugins
	 */
	protected function add_admin_submenu( string $menu_title ): void {
		\add_submenu_page(
			$this->menu_parent,
			$menu_title,
			$menu_title,
			'manage_options',
			$this->plugin_slug,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Common method: Render settings page with icon and sidebar
	 */
	protected function render_settings_page_base( string $plugin_name, string $icon_filename = 'icon-64x64.png' ): void {
		$plugin_dir = \plugin_dir_path( ( new \ReflectionClass( $this ) )->getFileName() );
		$icon_path  = $plugin_dir . 'assets/images/' . $icon_filename;
		$icon_url   = \plugin_dir_url( ( new \ReflectionClass( $this ) )->getFileName() ) . 'assets/images/' . $icon_filename;
		$has_icon   = \file_exists( $icon_path );
		?>
		<div class="wrap">
			<form method="post" action="options.php">
				<?php \settings_fields( $this->options_group ); ?>
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<div id="post-body-content">
							<?php foreach ( (array) $this->settings_blueprint as $section_id => $section ) : ?>
								<div class="timu-settings-section" style="margin-bottom:24px;">
									<div style="display:flex; align-items:center; gap:16px; padding:0 0 8px 0;">
										<?php if ( $has_icon ) : ?>
											<img src="<?php echo \esc_url( $icon_url ); ?>" width="48" height="48" style="border-radius:4px;" />
										<?php endif; ?>
										<h2 class="hndle" style="margin:0;"><?php echo \esc_html( $section['title'] ?? $plugin_name ); ?></h2>
									</div>
									<?php if ( ! empty( $section['description'] ) ) : ?>
										<p class="description" style="margin: 0 0 12px 0;">
											<?php echo \wp_kses_post( $section['description'] ); ?>
										</p>
									<?php endif; ?>
									<table class="form-table"><?php \do_settings_fields( $this->plugin_slug, $section_id ); ?></table>
								</div>
							<?php endforeach; ?>
							<?php \submit_button(); ?>
						</div>
						<div id="postbox-container-1" class="postbox-container">
							<div class="postbox">
								<div class="inside">
									<?php \do_action( 'timu_sidebar_under_banner', $this->plugin_slug ); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Get base settings blueprint (common across all plugins).
	 * Plugins should extend this with format-specific fields.
	 */
	public function get_base_blueprint(): array {
		return array(
			'config'   => array(
				'title'       => \__( 'Configuration', $this->plugin_slug ),
				'description' => \__( 'Basic plugin settings and conversion options.', $this->plugin_slug ),
				'fields'      => array(
					'enabled'       => array(
						'type'         => 'toggle',
						'label'        => \sprintf( \__( 'Enable %s Support', $this->plugin_slug ), \strtoupper( $this->get_data_prefix() ) ),
						'description'  => \__( 'Enable or disable this plugin functionality.', $this->plugin_slug ),
						'default'      => 1,
						'depends_on'   => array( 'enabled' => '1' ),
						'globalizable' => true,
					),
					'upload'        => array(
						'type'         => 'toggle',
						'label'        => \sprintf( \__( 'Allow Upload', $this->plugin_slug ), \strtoupper( $this->get_data_prefix() ) ),
						'description'  => \__( 'Allow or disallow uploading of this format.', $this->plugin_slug ),
						'default'      => 1,
						'depends_on'   => array( 'enabled' => '1' ),
						'globalizable' => true,
					),
					'target_format' => array(
						'type'         => 'radio',
						'label'        => \__( 'Convert to:', $this->plugin_slug ),
						'description'  => \__( 'Choose the target format for image conversion.', $this->plugin_slug ),
						'options'      => array(
							'webp' => 'WebP',
							'avif' => 'AVIF',
							'png'  => 'PNG',
							'jpg'  => 'JPG',
						),
						'descriptions' => array(
							'webp' => \__( 'Balanced modern standard. Perfect for fast loading.', $this->plugin_slug ),
							'avif' => \__( 'Extreme efficiency. The highest quality-to-filesize ratio available.', $this->plugin_slug ),
							'png'  => \__( 'Pixel-perfect lossless. Best for logos and transparency.', $this->plugin_slug ),
							'jpg'  => \__( 'Maximum compatibility. Best for generic photo support.', $this->plugin_slug ),
						),
						'default'      => $this->get_default_format(),
						'depends_on'   => array( 'enabled' => '1' ),
						'globalizable' => true,
					),
					'quality'       => array(
						'type'         => 'range',
						'label'        => \__( 'Optimization Quality', $this->plugin_slug ),
						'description'  => \__( 'Balance between file size and visual quality (higher = better quality, larger files).', $this->plugin_slug ),
						'default'      => 60,
						'min'          => 10,
						'max'          => 100,
						'step'         => 5,
						'depends_on'   => array( 'target_format' => array( 'avif', 'webp', 'png', 'jpg' ) ),
						'globalizable' => true,
					),
				),
			),
			'metadata' => array(
				'title'       => \__( 'Metadata Processing', $this->plugin_slug ),
				'description' => \__( 'Control how image metadata (EXIF, copyright, geo data) is processed during conversion.', $this->plugin_slug ),
				'fields'      => array(
					'strip_geo'     => array(
						'type'         => 'toggle',
						'label'        => \__( 'Strip Geo Metadata', $this->plugin_slug ),
						'description'  => \__( 'Remove location data (GPS, coordinates)', $this->plugin_slug ),
						'default'      => 1,
						'globalizable' => true,
					),
					'strip_private' => array(
						'type'         => 'toggle',
						'label'        => \__( 'Strip Private Metadata', $this->plugin_slug ),
						'description'  => \__( 'Remove private data (names, phone numbers, emails)', $this->plugin_slug ),
						'default'      => 1,
						'globalizable' => true,
					),
					'copyright'     => array(
						'type'         => 'text',
						'label'        => \__( 'Copyright Notice', $this->plugin_slug ),
						'description'  => \__( 'Copyright text to embed in image metadata', $this->plugin_slug ),
						'placeholder'  => '© 2026 Your Company',
						'default'      => '© ' . date( 'Y' ) . ' ' . \get_bloginfo( 'name' ),
						'globalizable' => true,
					),
					'website_url'   => array(
						'type'         => 'url',
						'label'        => \__( 'Website URL', $this->plugin_slug ),
						'description'  => \__( 'Website URL to embed in image metadata', $this->plugin_slug ),
						'placeholder'  => \home_url(),
						'default'      => \home_url(),
						'globalizable' => true,
					),
				),
			),
		);
	}

	/**
	 * Get default conversion format for this plugin.
	 */
	protected function get_default_format(): string {
		return match ( $this->get_data_prefix() ) {
			'avif'  => 'avif',
			'webp'  => 'webp',
			'bmp'   => 'png',
			'heic'  => 'jpg',
			'raw'   => 'jpg',
			'svg'   => 'png',
			'tiff'  => 'png',
			default => 'webp',
		};
	}

	/**
	 * Build format blueprint by merging custom sections with base blueprint.
	 *
	 * @param array $custom_sections Additional sections to merge with base blueprint
	 * @return array Complete blueprint with custom sections
	 */
	public function build_format_blueprint( array $custom_sections = array() ): array {
		$blueprint = $this->get_base_blueprint();

		if ( ! empty( $custom_sections ) ) {
			$blueprint = \array_merge( $blueprint, $custom_sections );
		}

		return $blueprint;
	}

	public function init_settings_generator( array $blueprint ): void {
		$this->settings_blueprint = $blueprint;
	}

	public function render_settings_page(): void {
		$this->admin?->render_settings_page();
	}

	/**
	 * Standard MIME type registration helper.
	 * Registers format-specific MIME types with WordPress.
	 *
	 * @param array $mimes Current MIME types array
	 * @param array $format_mimes Format-specific MIME types to register (e.g., ['avif' => 'image/avif'])
	 * @return array Updated MIME types array
	 */
	public function register_mime_types_standard( array $mimes, array $format_mimes ): array {
		if ( $this->get_option( 'enabled', 1 ) ) {
			$mimes = \array_merge( $mimes, $format_mimes );
		}
		return $mimes;
	}

	/**
	 * Standard upload processing helper.
	 * Processes uploads for format conversion based on MIME type.
	 *
	 * @param array $upload Upload data array
	 * @param array $supported_mimes Array of supported MIME types for this plugin
	 * @return array Processed upload data
	 */
	public function process_upload_standard( array $upload, array $supported_mimes ): array {
		if ( ! $this->get_option( 'enabled', 1 ) || empty( $upload['file'] ) ) {
			return $upload;
		}

		$mime = $upload['type'] ?? '';
		if ( ! in_array( $mime, $supported_mimes, true ) ) {
			return $upload;
		}

		// Process conversion via processor component
		if ( $this->processor ) {
			return $this->processor->process_image_upload( $upload );
		}

		return $upload;
	}
}

/**
 * --- SPOKE BASE ARCHITECT METADATA ---
 * Changelog:
 * - [1.2601.073000] Moved wp_support_v1 from Image's embedded core to Core plugin as TIMU_Spoke_Base. Namespace: TIMU\Core\Spoke. Enables DRY principle and simplifies spoke plugin development.
 * Upgrade Notice: Consolidated spoke framework for cleaner architecture
 * Requires at least: 6.4 | PHP: 8.2
 */
