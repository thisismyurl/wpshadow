<?php
/**
 * Feature: Simple Page Cache
 *
 * Serves static HTML files instead of regenerating pages on every visit.
 * Significantly improves performance by bypassing WordPress and PHP processing
 * for cached pages.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75002
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Simple_Cache
 *
 * Implements a simple page caching system that stores rendered HTML.
 */
final class WPSHADOW_Feature_Simple_Cache extends WPSHADOW_Abstract_Feature {

	/**
	 * Cache directory path.
	 *
	 * @var string
	 */
	private string $cache_dir;

	/**
	 * Cache expiration time in seconds.
	 *
	 * @var int
	 */
	private const CACHE_LIFETIME = 3600; // 1 hour

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'              => 'simple-cache',
				'name'            => __( 'Save Pages for Faster Loading', 'wpshadow' ),
				'description'     => __( 'Save a copy of your pages so they load instantly for visitors without rebuilding every time. Makes your site much faster.', 'wpshadow' ),
				'scope'           => 'core',
				'default_enabled' => false,
				'version'         => '1.0.0',
				'widget_group'    => 'performance',
				'aliases'         => array(
					'page cache',
					'caching',
					'speed up site',
					'faster loading',
					'performance optimization',
					'slow site',
					'static html',
					'page speed',
					'load time',
					'quick pages',
				),
				'sub_features'    => array(
					'cache_pages'         => __( 'Speed up your main pages', 'wpshadow' ),
					'cache_posts'         => __( 'Speed up your blog articles', 'wpshadow' ),
					'cache_archives'      => __( 'Speed up list and archive pages', 'wpshadow' ),
					'skip_logged_in'      => __( 'Show fresh content when you\'re editing', 'wpshadow' ),
					'skip_query_strings'  => __( 'Don\'t save search and filter results', 'wpshadow' ),
					'auto_clear_on_save'  => __( 'Update saved copy when you publish changes', 'wpshadow' ),
					'advanced_cache_keys' => __( 'Use advanced cache keys by device/role/cookies', 'wpshadow' ),
					'partial_cache'       => __( 'Cache selected page sections (fragments)', 'wpshadow' ),
					'cache_preload'       => __( 'Preload cache for popular pages', 'wpshadow' ),
					'cache_cdn'           => __( 'Enable CDN integration for cached files', 'wpshadow' ),
					'cache_warming'       => __( 'Warm cache in background when expired', 'wpshadow' ),
					'cache_compression'   => __( 'Compress cached HTML files (gzip)', 'wpshadow' ),
					'cache_mobile_split'  => __( 'Use separate cache for mobile visitors', 'wpshadow' ),
				),
			)
		);

		$this->register_default_settings(
			array(
				'cache_pages'         => true,
				'cache_posts'         => true,
				'cache_archives'      => true,
				'skip_logged_in'      => true,
				'skip_query_strings'  => true,
				'auto_clear_on_save'  => true,
				'advanced_cache_keys' => false,
				'partial_cache'       => false,
				'cache_preload'       => false,
				'cache_cdn'           => false,
				'cache_warming'       => false,
				'cache_compression'   => false,
				'cache_mobile_split'  => false,
			)
		);

		// Set cache directory per-site to avoid multisite collisions.
		$cache_base = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : ABSPATH . 'wp-content';
		$blog_id    = is_multisite() ? get_current_blog_id() : 0;
		$this->cache_dir = trailingslashit( $cache_base ) . 'uploads/wpshadow-cache';
		if ( $blog_id > 0 ) {
			$this->cache_dir .= '-' . $blog_id;
		}

		$this->log_activity( 'feature_initialized', 'Simple Cache feature initialized', 'info' );
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Create cache directory if needed
		$this->ensure_cache_directory();

		// Hook very early to check for cached content before WordPress loads templates
		add_action( 'template_redirect', array( $this, 'maybe_serve_cache' ), 1 );

		// Start output buffering to capture page content
		add_action( 'template_redirect', array( $this, 'start_output_buffering' ), 2 );

		// Clear cache on post save/update
		if ( $this->is_sub_feature_enabled( 'auto_clear_on_save', true ) ) {
			add_action( 'save_post', array( $this, 'clear_post_cache' ), 10, 1 );
			add_action( 'deleted_post', array( $this, 'clear_post_cache' ), 10, 1 );
			add_action( 'wp_trash_post', array( $this, 'clear_post_cache' ), 10, 1 );
		}

		// Clear cache on comment
		add_action( 'comment_post', array( $this, 'clear_post_cache_by_comment' ), 10, 1 );
		add_action( 'wp_set_comment_status', array( $this, 'clear_post_cache_by_comment' ), 10, 1 );

		// Clear cache on theme/plugin changes
		add_action( 'switch_theme', array( $this, 'clear_all_cache' ) );
		add_action( 'activated_plugin', array( $this, 'clear_all_cache' ) );
		add_action( 'deactivated_plugin', array( $this, 'clear_all_cache' ) );

		// Admin actions
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_button' ), 100 );
		add_action( 'admin_post_wpshadow_clear_cache', array( $this, 'admin_clear_cache' ) );
		add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );

		// Site health test
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		// WP-CLI commands
		if ( defined( 'WP_CLI' ) && 
			class_exists( '\WP_CLI' ) ) {
			$this->register_cli_commands();
		}
	}

	/**
	 * Ensure cache directory exists and is writable.
	 *
	 * @return bool
	 */
	private function ensure_cache_directory(): bool {
		if ( ! file_exists( $this->cache_dir ) ) {
			if ( ! wp_mkdir_p( $this->cache_dir ) ) {
				$this->log_activity( 'cache_error', 'Failed to create cache directory', 'error' );
				return false;
			}
		}

		// Create .htaccess to protect cache directory
		$htaccess_file = $this->cache_dir . '/.htaccess';
		if ( ! file_exists( $htaccess_file ) ) {
			$htaccess_content = "# WPShadow Cache Directory\n";
			$htaccess_content .= "<Files ~ \"\.html$\">\n";
			$htaccess_content .= "  Allow from all\n";
			$htaccess_content .= "</Files>\n";
			file_put_contents( $htaccess_file, $htaccess_content ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}

		// Create index.php to prevent directory listing
		$index_file = $this->cache_dir . '/index.php';
		if ( ! file_exists( $index_file ) ) {
			file_put_contents( $index_file, '<?php // Silence is golden' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}

		return true;
	}

	/**
	 * Check if current request should be cached.
	 *
	 * @return bool
	 */
	private function should_cache_request(): bool {
		// Don't cache if disabled
		if ( ! $this->is_enabled() ) {
			return false;
		}

		// Don't cache admin, login, or AJAX requests
		if ( is_admin() || is_user_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return false;
		}

		// Don't cache for logged-in users if setting enabled
		if ( $this->is_sub_feature_enabled( 'skip_logged_in', true ) && is_user_logged_in() ) {
			return false;
		}

		// Don't cache POST requests
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			return false;
		}

		// Don't cache requests with query strings if setting enabled
		if ( $this->is_sub_feature_enabled( 'skip_query_strings', true ) && ! empty( $_SERVER['QUERY_STRING'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			return false;
		}

		// Check if current page type should be cached
		if ( is_singular() && ! is_front_page() ) {
			if ( is_page() && ! $this->is_sub_feature_enabled( 'cache_pages', true ) ) {
				return false;
			}
			if ( is_single() && ! $this->is_sub_feature_enabled( 'cache_posts', true ) ) {
				return false;
			}
		}

		if ( is_archive() && ! $this->is_sub_feature_enabled( 'cache_archives', true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get cache file path for current request.
	 *
	 * @return string
	 */
	private function get_cache_file_path(): string {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$host        = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';

		// Create a unique cache key
		$cache_key = md5( $host . $request_uri );

		return $this->cache_dir . '/' . $cache_key . '.html';
	}

	/**
	 * Check for cached page and serve it if valid.
	 *
	 * @return void
	 */
	public function maybe_serve_cache(): void {
		if ( ! $this->should_cache_request() ) {
			return;
		}

		$cache_file = $this->get_cache_file_path();

		// Check if cache file exists and is still valid
		if ( file_exists( $cache_file ) ) {
			$file_age = time() - filemtime( $cache_file );

			if ( $file_age < self::CACHE_LIFETIME ) {
				// Serve cached content
				header( 'X-WPShadow-Cache: HIT' );
				header( 'X-Cache-Age: ' . $file_age . 's' );
				header( 'Cache-Control: public, max-age=' . ( self::CACHE_LIFETIME - $file_age ) );
						do_action( 'wpshadow_simple_cache_hit', $this->get_current_url(), $cache_file, $file_age );

				// Read and output cached content
				readfile( $cache_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile

				$this->log_activity( 'cache_hit', 'Served cached page: ' . $this->get_current_url(), 'info' );

				exit;
			} else {
				// Cache expired, delete it
				unlink( $cache_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_unlink
				$this->log_activity( 'cache_expired', 'Cache expired for: ' . $this->get_current_url(), 'info' );
				do_action( 'wpshadow_simple_cache_expired', $this->get_current_url(), $cache_file );
			}
		}

		// Mark that this is a cache miss
		header( 'X-WPShadow-Cache: MISS' );
		do_action( 'wpshadow_simple_cache_miss', $this->get_current_url() );
	}

	/**
	 * Start output buffering to capture page content.
	 *
	 * @return void
	 */
	public function start_output_buffering(): void {
		if ( ! $this->should_cache_request() ) {
			return;
		}

		ob_start( array( $this, 'cache_output_buffer' ) );
	}

	/**
	 * Cache the output buffer content.
	 *
	 * @param string $buffer Output buffer content.
	 * @return string Unmodified buffer content.
	 */
	public function cache_output_buffer( string $buffer ): string {
		// Only cache if we have substantial content
		if ( empty( $buffer ) || strlen( $buffer ) < 255 ) {
			return $buffer;
		}

		// Don't cache if there are errors
		$status = http_response_code();
		if ( $status >= 400 ) {
			return $buffer;
		}

		$cache_file = $this->get_cache_file_path();

		// Add cache metadata comment
		$metadata = sprintf(
			"\n<!-- WPShadow Cache | Generated: %s | Expires: %s | URL: %s -->\n",
			gmdate( 'Y-m-d H:i:s' ),
			gmdate( 'Y-m-d H:i:s', time() + self::CACHE_LIFETIME ),
			esc_html( $this->get_current_url() )
		);

		// Insert metadata before </body> tag if it exists
		if ( stripos( $buffer, '</body>' ) !== false ) {
			$buffer_to_save = str_ireplace( '</body>', $metadata . '</body>', $buffer );
		} else {
			$buffer_to_save = $buffer . $metadata;
		}

		// Save to cache file
		$saved = file_put_contents( $cache_file, $buffer_to_save ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

		if ( $saved !== false ) {
			$this->log_activity( 'cache_saved', sprintf( 'Cached page (%s KB): %s', number_format( strlen( $buffer_to_save ) / 1024, 2 ), $this->get_current_url() ), 'info' );
			do_action( 'wpshadow_simple_cache_saved', $this->get_current_url(), $cache_file );
		}

		// Return original buffer unchanged
		return $buffer;
	}

	/**
	 * Get the current URL for logging and cache keys.
	 *
	 * @return string
	 */
	private function get_current_url(): string {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$host        = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		
		return $host . $request_uri;
	}

	/**
	 * Clear cache for a specific post.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function clear_post_cache( int $post_id ): void {
		// Clear all cache to be safe (more sophisticated cache key mapping could be added)
		$this->clear_all_cache();

		$this->log_activity( 'cache_cleared', sprintf( 'Cleared cache for post ID %d', $post_id ), 'info' );
	}

	/**
	 * Clear cache when a comment is posted.
	 *
	 * @param int $comment_id Comment ID.
	 * @return void
	 */
	public function clear_post_cache_by_comment( int $comment_id ): void {
		$comment = get_comment( $comment_id );
		if ( $comment && $comment->comment_post_ID ) {
			$this->clear_post_cache( (int) $comment->comment_post_ID );
		}
	}

	/**
	 * Clear all cached files.
	 *
	 * @return int Number of files deleted.
	 */
	public function clear_all_cache(): int {
		$deleted = 0;

		if ( ! is_dir( $this->cache_dir ) ) {
			return 0;
		}

		$files = glob( $this->cache_dir . '/*.html' );

		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				if ( is_file( $file ) ) {
					unlink( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_unlink
					$deleted++;
				}
			}
		}

		$this->log_activity( 'cache_cleared', sprintf( 'Cleared %d cached files', $deleted ), 'info' );

		return $deleted;
	}

	/**
	 * Add clear cache button to admin bar.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public function add_admin_bar_button( $wp_admin_bar ): void {
		if ( is_network_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'wpshadow-clear-cache',
				'title' => __( 'Clear Page Cache', 'wpshadow' ),
				'href'  => wp_nonce_url( admin_url( 'admin-post.php?action=wpshadow_clear_cache' ), 'wpshadow_clear_cache' ),
				'meta'  => array(
					'title' => __( 'Clear all cached pages', 'wpshadow' ),
				),
			)
		);
	}

	/**
	 * Display admin notice after cache clear.
	 *
	 * @return void
	 */
	public function display_admin_notices(): void {
		if ( isset( $_GET['wpshadow-cache-cleared'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$deleted = intval( $_GET['wpshadow-cache-cleared'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					printf(
						/* translators: %d: Number of cached files deleted */
						esc_html( _n( 'Cleared %d cached page.', 'Cleared %d cached pages.', $deleted, 'wpshadow' ) ),
						esc_html( number_format_i18n( $deleted ) )
					);
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Get cache statistics.
	 *
	 * @return array<string, mixed>
	 */
	public function get_cache_stats(): array {
		$stats = array(
			'total_files' => 0,
			'total_size'  => 0,
			'oldest_file' => null,
			'newest_file' => null,
		);

		if ( ! is_dir( $this->cache_dir ) ) {
			return $stats;
		}

		$files = glob( $this->cache_dir . '/*.html' );

		if ( ! is_array( $files ) ) {
			return $stats;
		}

		$stats['total_files'] = count( $files );
		$oldest_time          = PHP_INT_MAX;
		$newest_time          = 0;

		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				$stats['total_size'] += filesize( $file );
				$mtime                = filemtime( $file );

				if ( $mtime < $oldest_time ) {
					$oldest_time           = $mtime;
					$stats['oldest_file'] = $mtime;
				}

				if ( $mtime > $newest_time ) {
					$newest_time           = $mtime;
					$stats['newest_file'] = $mtime;
				}
			}
		}

		return $stats;
	}

	/**
	 * Handle admin clear cache request.
	 *
	 * @return void
	 */
	public function admin_clear_cache(): void {
		check_admin_referer( 'wpshadow_clear_cache' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'wpshadow' ) );
		}

		$deleted = $this->clear_all_cache();
		do_action( 'wpshadow_simple_cache_cleared', $deleted );

		$redirect_url = wp_get_referer();
		if ( ! $redirect_url ) {
			$redirect_url = admin_url();
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'wpshadow-cache-cleared' => $deleted,
				),
				$redirect_url
			)
		);
		exit;
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, array<string, mixed>> $tests Site Health tests.
	 * @return array<string, array<string, mixed>>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['wpshadow_simple_cache'] = array(
			'label' => __( 'Simple Page Cache', 'wpshadow' ),
			'test'  => array( $this, 'site_health_test' ),
		);

		return $tests;
	}

	/**
	 * Register WP-CLI commands for cache management.
	 */
	private function register_cli_commands(): void {
		if ( ! class_exists( '\WP_CLI' ) ) {
			return;
		}

		$feature = $this;

		\WP_CLI::add_command(
			'wpshadow cache',
			new class( $feature ) {
				private WPSHADOW_Feature_Simple_Cache $feature;

				public function __construct( WPSHADOW_Feature_Simple_Cache $feature ) {
					$this->feature = $feature;
				}

				/**
				 * Clear all cached files.
				 *
				 * ## OPTIONS
				 * [--network]
				 * : Run on the network (multisite) using --url for a specific site.
				 *
				 * ## EXAMPLES
				 * wp wpshadow cache clear
				 */
				public function clear( array $args, array $assoc_args ): void {
					$deleted = $this->feature->clear_all_cache();
					\WP_CLI::success( sprintf( '%d cached files deleted.', $deleted ) );
				}

				/**
				 * Show cache status and statistics.
				 */
				public function status( array $args, array $assoc_args ): void {
					$stats = $this->feature->get_cache_stats();
					\WP_CLI::line( sprintf( 'Files: %d', $stats['total_files'] ) );
					\WP_CLI::line( sprintf( 'Size: %s', size_format( $stats['total_size'] ) ) );
				}
			}
		);
	}

	/**
	 * Site Health test callback.
	 *
	 * @return array<string, mixed>
	 */
	public function site_health_test(): array {
		$result = array(
			'label'       => __( 'Page cache is working', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'The simple page cache is enabled and working correctly.', 'wpshadow' )
			),
			'actions'     => '',
			'test'        => 'wpshadow_simple_cache',
		);

		if ( ! $this->is_enabled() ) {
			$result['status']      = 'recommended';
			$result['label']       = __( 'Page cache is not enabled', 'wpshadow' );
			$result['description'] = sprintf(
				'<p>%s</p>',
				__( 'Enable the simple page cache to make your site load faster by saving copies of your pages.', 'wpshadow' )
			);
			$result['actions'] = sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=wpshadow&wpshadow_tab=features' ),
				__( 'Enable Page Cache', 'wpshadow' )
			);
		} elseif ( ! is_dir( $this->cache_dir ) || ! is_writable( $this->cache_dir ) ) {
			$result['status']      = 'critical';
			$result['label']       = __( 'Cache directory is not writable', 'wpshadow' );
			$result['description'] = sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %s: Cache directory path */
					__( 'The cache directory at %s is not writable. Please check file permissions.', 'wpshadow' ),
					'<code>' . esc_html( $this->cache_dir ) . '</code>'
				)
			);
		} else {
			// Get cache statistics
			$stats = $this->get_cache_stats();

			$result['description'] = sprintf(
				'<p>%s</p><ul><li>%s</li><li>%s</li><li>%s</li></ul>',
				__( 'The page cache is active and storing rendered pages:', 'wpshadow' ),
				sprintf(
					/* translators: %d: Number of cached files */
					_n( '%d page cached', '%d pages cached', $stats['total_files'], 'wpshadow' ),
					number_format_i18n( $stats['total_files'] )
				),
				sprintf(
					/* translators: %s: Cache size in MB */
					__( 'Cache size: %s', 'wpshadow' ),
					size_format( $stats['total_size'] )
				),
				sprintf(
					/* translators: %d: Cache lifetime in hours */
					__( 'Cache lifetime: %d hour', 'wpshadow' ),
					self::CACHE_LIFETIME / 3600
				)
			);

			$result['actions'] = sprintf(
				'<a href="%s">%s</a>',
				wp_nonce_url( admin_url( 'admin-post.php?action=wpshadow_clear_cache' ), 'wpshadow_clear_cache' ),
				__( 'Clear Cache', 'wpshadow' )
			);
		}

		return $result;
	}
}
