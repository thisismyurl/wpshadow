<?php
/**
 * Feature: Troubleshooting Mode
 *
 * Provides a safe troubleshooting mode that allows administrators to:
 * - Test the site with default theme and no plugins
 * - Test with only specific plugins enabled
 * - Isolate plugin/theme conflicts without affecting visitors
 * - Automatically restore settings after troubleshooting
 *
 * Similar to Health Check & Troubleshooting plugin's troubleshooting mode.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.74000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Feature_Troubleshooting_Mode
 *
 * Safe troubleshooting mode implementation.
 */
final class WPSHADOW_Feature_Troubleshooting_Mode extends WPSHADOW_Abstract_Feature {

	/**
	 * Cookie name for troubleshooting mode.
	 */
	private const COOKIE_NAME = 'wpshadow_troubleshooting_mode';

	/**
	 * Transient key for troubleshooting state.
	 */
	private const TRANSIENT_KEY = 'wpshadow_troubleshooting_state_';

	/**
	 * Cookie lifetime (24 hours).
	 */
	private const COOKIE_LIFETIME = 86400;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'troubleshooting-mode',
			'name'               => __( 'Private Testing Mode', 'plugin-wpshadow' ),
			'description'        => __( 'Tests changes with plugins disabled or different themes applied only to your view while visitors still see your normal site, perfect for finding conflicts or problems without risking downtime. Lets you isolate plugin issues, preview theme changes safely, and debug errors in a live environment without affecting anyone else, so you can troubleshoot confidently on a production site.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'debugging',
				'widget_label'       => __( 'Debugging & Diagnostics', 'plugin-wpshadow' ),
				'widget_description' => __( 'Tools for diagnosing and resolving site conflicts', 'plugin-wpshadow' ),
				// Unified metadata.
				'license_level'      => 1, // Free for everyone.
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-admin-tools',
				'category'           => 'debugging',
				'priority'           => 5, // High priority.
				'dashboard'          => 'overview',
				'widget_column'      => 'left',
				'widget_priority'    => 5,
			)
		);
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

		// Admin menu.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// AJAX handlers.
		add_action( 'wp_ajax_WPSHADOW_troubleshooting_start', array( $this, 'ajax_start_troubleshooting' ) );
		add_action( 'wp_ajax_WPSHADOW_troubleshooting_stop', array( $this, 'ajax_stop_troubleshooting' ) );
		add_action( 'wp_ajax_WPSHADOW_troubleshooting_toggle_plugin', array( $this, 'ajax_toggle_plugin' ) );

		// Apply filters if in troubleshooting mode.
		if ( $this->is_troubleshooting_active() ) {
			add_filter( 'option_active_plugins', array( $this, 'filter_active_plugins' ), 999 );
			add_filter( 'stylesheet', array( $this, 'filter_stylesheet' ), 999 );
			add_filter( 'template', array( $this, 'filter_template' ), 999 );
			add_action( 'admin_notices', array( $this, 'troubleshooting_notice' ) );
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_notice' ), 1000 );
		}
	}

	/**
	 * Add admin menu.
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		add_submenu_page(
			'wpshadow',
			__( 'Troubleshooting Mode', 'plugin-wpshadow' ),
			__( 'Troubleshooting', 'plugin-wpshadow' ),
			'manage_options',
			'wpshadow-troubleshooting',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Check if troubleshooting mode is active for current user.
	 *
	 * @return bool True if active.
	 */
	private function is_troubleshooting_active(): bool {
		// Check cookie first (fast path).
		if ( ! isset( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			return false;
		}

		$token = sanitize_key( $_COOKIE[ self::COOKIE_NAME ] );
		if ( empty( $token ) ) {
			return false;
		}

		// Verify user ID from token.
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return false;
		}

		// Check transient state.
		$state = get_transient( self::TRANSIENT_KEY . $user_id );
		if ( false === $state || ! is_array( $state ) ) {
			return false;
		}

		// Verify token matches.
		if ( empty( $state['token'] ) || $state['token'] !== $token ) {
			return false;
		}

		return true;
	}

	/**
	 * Get troubleshooting state for current user.
	 *
	 * @return array|false State array or false if not active.
	 */
	private function get_troubleshooting_state() {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return false;
		}

		$state = get_transient( self::TRANSIENT_KEY . $user_id );
		if ( false === $state || ! is_array( $state ) ) {
			return false;
		}

		return $state;
	}

	/**
	 * Save troubleshooting state for current user.
	 *
	 * @param array $state State to save.
	 * @return bool Success.
	 */
	private function save_troubleshooting_state( array $state ): bool {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return false;
		}

		return set_transient( self::TRANSIENT_KEY . $user_id, $state, self::COOKIE_LIFETIME );
	}

	/**
	 * Filter active plugins in troubleshooting mode.
	 *
	 * @param array $plugins Active plugins list.
	 * @return array Filtered plugins list.
	 */
	public function filter_active_plugins( $plugins ): array {
		if ( ! is_array( $plugins ) ) {
			return array();
		}

		$state = $this->get_troubleshooting_state();
		if ( false === $state ) {
			return $plugins;
		}

		// Always keep WPShadow active.
		$keep_plugins = array( 'plugin-wpshadow/wpshadow.php' );

		// Add enabled plugins from state.
		if ( ! empty( $state['enabled_plugins'] ) && is_array( $state['enabled_plugins'] ) ) {
			$keep_plugins = array_merge( $keep_plugins, $state['enabled_plugins'] );
		}

		// Return only plugins we want to keep.
		return array_intersect( $plugins, $keep_plugins );
	}

	/**
	 * Filter stylesheet to default theme.
	 *
	 * @param string $stylesheet Current stylesheet.
	 * @return string Filtered stylesheet.
	 */
	public function filter_stylesheet( $stylesheet ): string {
		$state = $this->get_troubleshooting_state();
		if ( false === $state ) {
			return $stylesheet;
		}

		// If custom theme set, use it.
		if ( ! empty( $state['theme'] ) ) {
			return sanitize_key( $state['theme'] );
		}

		// Otherwise use a default WordPress theme.
		return $this->get_default_theme();
	}

	/**
	 * Filter template to default theme.
	 *
	 * @param string $template Current template.
	 * @return string Filtered template.
	 */
	public function filter_template( $template ): string {
		$state = $this->get_troubleshooting_state();
		if ( false === $state ) {
			return $template;
		}

		// If custom theme set, determine its parent.
		if ( ! empty( $state['theme'] ) ) {
			$theme  = wp_get_theme( $state['theme'] );
			$parent = $theme->get( 'Template' );
			return ! empty( $parent ) ? sanitize_key( $parent ) : sanitize_key( $state['theme'] );
		}

		// Otherwise use a default WordPress theme.
		return $this->get_default_theme();
	}

	/**
	 * Get a default WordPress theme.
	 *
	 * @return string Theme slug.
	 */
	private function get_default_theme(): string {
		$default_themes = array( 'twentytwentyfour', 'twentytwentythree', 'twentytwentytwo', 'twentytwentyone', 'twentytwenty' );

		foreach ( $default_themes as $theme_slug ) {
			$theme = wp_get_theme( $theme_slug );
			if ( $theme->exists() ) {
				return $theme_slug;
			}
		}

		// Fallback to current theme if no default available.
		return get_stylesheet();
	}

	/**
	 * Show admin notice when in troubleshooting mode.
	 *
	 * @return void
	 */
	public function troubleshooting_notice(): void {
		$state = $this->get_troubleshooting_state();
		if ( false === $state ) {
			return;
		}

		$enabled_count = ! empty( $state['enabled_plugins'] ) ? count( $state['enabled_plugins'] ) : 0;

		?>
		<div class="notice notice-warning is-dismissible wps-troubleshooting-notice" style="border-left: 4px solid #f0ad4e;">
			<p>
				<strong><?php esc_html_e( '🔧 Troubleshooting Mode Active', 'plugin-wpshadow' ); ?></strong>
				<br>
				<?php
				printf(
					/* translators: %d: number of plugins */
					esc_html( _n( '%d plugin enabled', '%d plugins enabled', $enabled_count, 'plugin-wpshadow' ) ),
					esc_html( $enabled_count )
				);
				?>
				<?php if ( ! empty( $state['theme'] ) ) : ?>
					<?php
					printf(
						/* translators: %s: theme name */
						esc_html__( ' | Testing with theme: %s', 'plugin-wpshadow' ),
						esc_html( $state['theme'] )
					);
					?>
				<?php else : ?>
					<?php esc_html_e( ' | Using default WordPress theme', 'plugin-wpshadow' ); ?>
				<?php endif; ?>
			</p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-troubleshooting' ) ); ?>" class="button button-small">
					<?php esc_html_e( 'Manage Troubleshooting', 'plugin-wpshadow' ); ?>
				</a>
				<button type="button" id="wps-stop-troubleshooting-quick" class="button button-small button-link-delete">
					<?php esc_html_e( 'Disable Troubleshooting Mode', 'plugin-wpshadow' ); ?>
				</button>
			</p>
		</div>
		<script>
		jQuery(document).ready(function($) {
			$('#wps-stop-troubleshooting-quick').on('click', function(e) {
				e.preventDefault();
				if (confirm('<?php echo esc_js( __( 'Exit troubleshooting mode and restore normal site functionality?', 'plugin-wpshadow' ) ); ?>')) {
					$.post(ajaxurl, {
						action: 'wpshadow_troubleshooting_stop',
						nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_troubleshooting' ) ); ?>'
					}, function() {
						window.location.reload();
					});
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * Add admin bar notice.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public function admin_bar_notice( $wp_admin_bar ): void {
		$state = $this->get_troubleshooting_state();
		if ( false === $state ) {
			return;
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'wps-troubleshooting-mode',
				'title' => '<span style="color: #f0ad4e;">🔧 ' . esc_html__( 'Troubleshooting Mode', 'plugin-wpshadow' ) . '</span>',
				'href'  => admin_url( 'admin.php?page=wpshadow-troubleshooting' ),
			)
		);
	}

	/**
	 * AJAX: Start troubleshooting mode.
	 *
	 * @return void
	 */
	public function ajax_start_troubleshooting(): void {
		\WPShadow\WPSHADOW_verify_ajax_request( 'wpshadow_troubleshooting' );

		$user_id = get_current_user_id();
		$token   = wp_generate_password( 32, false );

		$state = array(
			'token'           => $token,
			'enabled_plugins' => array(), // Start with no plugins.
			'theme'           => '', // Use default theme.
			'started_at'      => time(),
		);

		$this->save_troubleshooting_state( $state );

		// Set cookie with SameSite attribute to prevent CSRF.
		setcookie(
			self::COOKIE_NAME,
			$token,
			array(
				'expires'  => time() + self::COOKIE_LIFETIME,
				'path'     => COOKIEPATH,
				'domain'   => COOKIE_DOMAIN,
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);

		wp_send_json_success(
			array(
				'message'  => __( 'Troubleshooting mode activated', 'plugin-wpshadow' ),
				'redirect' => admin_url( 'admin.php?page=wpshadow-troubleshooting' ),
			)
		);
	}

	/**
	 * AJAX: Stop troubleshooting mode.
	 *
	 * @return void
	 */
	public function ajax_stop_troubleshooting(): void {
		\WPShadow\WPSHADOW_verify_ajax_request( 'wpshadow_troubleshooting' );

		$user_id = get_current_user_id();
		delete_transient( self::TRANSIENT_KEY . $user_id );

		// Clear cookie with SameSite attribute.
		setcookie(
			self::COOKIE_NAME,
			'',
			array(
				'expires'  => time() - 3600,
				'path'     => COOKIEPATH,
				'domain'   => COOKIE_DOMAIN,
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);

		wp_send_json_success(
			array(
				'message'  => __( 'Troubleshooting mode deactivated', 'plugin-wpshadow' ),
				'redirect' => admin_url( 'admin.php?page=wpshadow-troubleshooting' ),
			)
		);
	}

	/**
	 * AJAX: Toggle plugin in troubleshooting mode.
	 *
	 * @return void
	 */
	public function ajax_toggle_plugin(): void {
		\WPShadow\WPSHADOW_verify_ajax_request( 'wpshadow_troubleshooting' );

		$plugin = \WPShadow\WPSHADOW_get_post_text( 'plugin' );
		$enable = ! empty( $_POST['enable'] );

		if ( empty( $plugin ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid plugin', 'plugin-wpshadow' ) ) );
		}

		$state = $this->get_troubleshooting_state();
		if ( false === $state ) {
			wp_send_json_error( array( 'message' => __( 'Troubleshooting mode not active', 'plugin-wpshadow' ) ) );
		}

		if ( ! isset( $state['enabled_plugins'] ) ) {
			$state['enabled_plugins'] = array();
		}

		if ( $enable ) {
			if ( ! in_array( $plugin, $state['enabled_plugins'], true ) ) {
				$state['enabled_plugins'][] = $plugin;
			}
		} else {
			$state['enabled_plugins'] = array_diff( $state['enabled_plugins'], array( $plugin ) );
		}

		$this->save_troubleshooting_state( $state );

		wp_send_json_success(
			array(
				'message' => $enable
					? __( 'Plugin enabled in troubleshooting mode', 'plugin-wpshadow' )
					: __( 'Plugin disabled in troubleshooting mode', 'plugin-wpshadow' ),
			)
		);
	}

	/**
	 * Render troubleshooting page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		$is_active = $this->is_troubleshooting_active();
		$state     = $this->get_troubleshooting_state();

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Troubleshooting Mode', 'plugin-wpshadow' ); ?></h1>

			<div class="card">
				<h2><?php esc_html_e( 'What is Troubleshooting Mode?', 'plugin-wpshadow' ); ?></h2>
				<p>
					<?php esc_html_e( 'Troubleshooting Mode allows you to test your site with plugins disabled and a default theme, without affecting your visitors. This is perfect for:', 'plugin-wpshadow' ); ?>
				</p>
				<ul style="list-style: disc; margin-left: 20px;">
					<li><?php esc_html_e( 'Isolating plugin conflicts', 'plugin-wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Testing theme compatibility', 'plugin-wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Debugging errors safely', 'plugin-wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Testing updates before applying site-wide', 'plugin-wpshadow' ); ?></li>
				</ul>
				<p>
					<strong><?php esc_html_e( 'Important:', 'plugin-wpshadow' ); ?></strong>
					<?php esc_html_e( 'Changes only affect your browser session. Visitors see the normal site.', 'plugin-wpshadow' ); ?>
				</p>
			</div>

			<?php if ( ! $is_active ) : ?>
				<div class="card">
					<h2><?php esc_html_e( 'Enable Troubleshooting Mode', 'plugin-wpshadow' ); ?></h2>
					<p><?php esc_html_e( 'Click the button below to start troubleshooting mode. All plugins except WPShadow will be disabled for your session only.', 'plugin-wpshadow' ); ?></p>
					<p>
						<button type="button" id="wps-start-troubleshooting" class="button button-primary button-hero">
							<?php esc_html_e( 'Enable Troubleshooting Mode', 'plugin-wpshadow' ); ?>
						</button>
					</p>
				</div>
			<?php else : ?>
				<div class="notice notice-warning inline" style="margin: 20px 0;">
					<p>
						<strong><?php esc_html_e( '🔧 Troubleshooting Mode is Active', 'plugin-wpshadow' ); ?></strong>
					</p>
				</div>

				<div class="card">
					<h2><?php esc_html_e( 'Manage Plugins', 'plugin-wpshadow' ); ?></h2>
					<p><?php esc_html_e( 'Enable plugins one at a time to isolate conflicts:', 'plugin-wpshadow' ); ?></p>

					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th style="width: 50px;"><?php esc_html_e( 'Status', 'plugin-wpshadow' ); ?></th>
								<th><?php esc_html_e( 'Plugin', 'plugin-wpshadow' ); ?></th>
								<th><?php esc_html_e( 'Action', 'plugin-wpshadow' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$all_plugins     = get_plugins();
							$enabled_plugins = ! empty( $state['enabled_plugins'] ) ? $state['enabled_plugins'] : array();

							foreach ( $all_plugins as $plugin_file => $plugin_data ) :
								// Skip WPShadow itself.
								if ( strpos( $plugin_file, 'wpshadow' ) !== false ) {
									continue;
								}

								$is_enabled = in_array( $plugin_file, $enabled_plugins, true );
								?>
								<tr>
									<td>
										<?php if ( $is_enabled ) : ?>
											<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
										<?php else : ?>
											<span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span>
										<?php endif; ?>
									</td>
									<td>
										<strong><?php echo esc_html( $plugin_data['Name'] ); ?></strong>
										<?php if ( ! empty( $plugin_data['Version'] ) ) : ?>
											<br><small><?php echo esc_html( sprintf( __( 'Version %s', 'plugin-wpshadow' ), $plugin_data['Version'] ) ); ?></small>
										<?php endif; ?>
									</td>
									<td>
										<button
											type="button"
											class="button button-small wps-toggle-plugin"
											data-plugin="<?php echo esc_attr( $plugin_file ); ?>"
											data-action="<?php echo $is_enabled ? 'disable' : 'enable'; ?>"
										>
											<?php echo $is_enabled ? esc_html__( 'Disable', 'plugin-wpshadow' ) : esc_html__( 'Enable', 'plugin-wpshadow' ); ?>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<div class="card">
					<h2><?php esc_html_e( 'Exit Troubleshooting Mode', 'plugin-wpshadow' ); ?></h2>
					<p><?php esc_html_e( 'When finished troubleshooting, click below to restore normal site functionality:', 'plugin-wpshadow' ); ?></p>
					<p>
						<button type="button" id="wps-stop-troubleshooting" class="button button-secondary">
							<?php esc_html_e( 'Disable Troubleshooting Mode', 'plugin-wpshadow' ); ?>
						</button>
					</p>
				</div>
			<?php endif; ?>
		</div>

		<script>
		jQuery(document).ready(function($) {
			const nonce = '<?php echo esc_js( wp_create_nonce( 'wpshadow_troubleshooting' ) ); ?>';

			// Start troubleshooting.
			$('#wps-start-troubleshooting').on('click', function(e) {
				e.preventDefault();
				const $button = $(this);
				$button.prop('disabled', true).text('<?php echo esc_js( __( 'Activating...', 'plugin-wpshadow' ) ); ?>');

				$.post(ajaxurl, {
					action: 'wpshadow_troubleshooting_start',
					nonce: nonce
				}, function(response) {
					if (response.success) {
						window.location.reload();
					} else {
						alert(response.data.message || '<?php echo esc_js( __( 'Error activating troubleshooting mode', 'plugin-wpshadow' ) ); ?>');
						$button.prop('disabled', false).text('<?php echo esc_js( __( 'Enable Troubleshooting Mode', 'plugin-wpshadow' ) ); ?>');
					}
				});
			});

			// Stop troubleshooting.
			$('#wps-stop-troubleshooting').on('click', function(e) {
				e.preventDefault();
				if (!confirm('<?php echo esc_js( __( 'Exit troubleshooting mode and restore normal site functionality?', 'plugin-wpshadow' ) ); ?>')) {
					return;
				}

				const $button = $(this);
				$button.prop('disabled', true).text('<?php echo esc_js( __( 'Deactivating...', 'plugin-wpshadow' ) ); ?>');

				$.post(ajaxurl, {
					action: 'wpshadow_troubleshooting_stop',
					nonce: nonce
				}, function(response) {
					if (response.success) {
						window.location.reload();
					} else {
						alert(response.data.message || '<?php echo esc_js( __( 'Error deactivating troubleshooting mode', 'plugin-wpshadow' ) ); ?>');
						$button.prop('disabled', false).text('<?php echo esc_js( __( 'Disable Troubleshooting Mode', 'plugin-wpshadow' ) ); ?>');
					}
				});
			});

			// Toggle plugin.
			$('.wps-toggle-plugin').on('click', function(e) {
				e.preventDefault();
				const $button = $(this);
				const plugin = $button.data('plugin');
				const action = $button.data('action');
				const originalText = $button.text();

				$button.prop('disabled', true).text('<?php echo esc_js( __( 'Working...', 'plugin-wpshadow' ) ); ?>');

				$.post(ajaxurl, {
					action: 'wpshadow_troubleshooting_toggle_plugin',
					nonce: nonce,
					plugin: plugin,
					enable: action === 'enable'
				}, function(response) {
					if (response.success) {
						window.location.reload();
					} else {
						alert(response.data.message || '<?php echo esc_js( __( 'Error toggling plugin', 'plugin-wpshadow' ) ); ?>');
						$button.prop('disabled', false).text(originalText);
					}
				});
			});
		});
		</script>
		<?php
	}
}
