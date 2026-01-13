<?php
/**
 * Feature: Conflict Sandbox (Per-User Conflict Isolation)
 *
 * Provides per-user plugin deactivation and theme switching for debugging
 * without affecting visitors. Allows admins to isolate plugin conflicts
 * in real-time while the site remains normal for all visitors.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport\Features;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPS_Feature_Conflict_Sandbox
 *
 * Per-user conflict isolation implementation.
 */
final class WPS_Feature_Conflict_Sandbox extends WPS_Abstract_Feature {

	/**
	 * Cookie name for sandbox mode.
	 */
	private const COOKIE_NAME = 'wps_conflict_sandbox';

	/**
	 * Transient prefix for sandbox state.
	 */
	private const TRANSIENT_PREFIX = 'wps_sandbox_state_';

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                  => 'conflict-sandbox',
				'name'                => __( 'Conflict Sandbox', 'plugin-wp-support-thisismyurl' ),
				'description'         => __( 'Per-user plugin deactivation and theme switching for debugging without affecting visitors', 'plugin-wp-support-thisismyurl' ),
				'scope'               => 'core',
				'default_enabled'     => false,
				'version'             => '1.0.0',
				'widget_group'        => 'debugging',
				'widget_label'        => __( 'Debugging & Diagnostics', 'plugin-wp-support-thisismyurl' ),
				'widget_description'  => __( 'Advanced debugging features for troubleshooting', 'plugin-wp-support-thisismyurl' ),
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

		// Admin menu for sandbox controls.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Handle AJAX actions for sandbox management.
		add_action( 'wp_ajax_wps_sandbox_toggle', array( $this, 'ajax_toggle_sandbox' ) );
		add_action( 'wp_ajax_wps_sandbox_toggle_plugin', array( $this, 'ajax_toggle_plugin' ) );
		add_action( 'wp_ajax_wps_sandbox_switch_theme', array( $this, 'ajax_switch_theme' ) );
		add_action( 'wp_ajax_wps_sandbox_clear', array( $this, 'ajax_clear_sandbox' ) );

		// Apply sandbox filters if user is in sandbox mode.
		if ( $this->is_sandbox_active() ) {
			add_filter( 'option_active_plugins', array( $this, 'filter_active_plugins' ), 999 );
			add_filter( 'stylesheet', array( $this, 'filter_stylesheet' ), 999 );
			add_filter( 'template', array( $this, 'filter_template' ), 999 );

			// Add admin notice when in sandbox mode.
			add_action( 'admin_notices', array( $this, 'sandbox_mode_notice' ) );
		}

		// Initialize sandbox session on login.
		add_action( 'init', array( $this, 'init_sandbox_session' ), 5 );
	}

	/**
	 * Add admin menu for sandbox controls.
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		add_submenu_page(
			'wp-support',
			__( 'Conflict Sandbox', 'plugin-wp-support-thisismyurl' ),
			__( 'Conflict Sandbox', 'plugin-wp-support-thisismyurl' ),
			'manage_options',
			'wp-support-conflict-sandbox',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Initialize sandbox session.
	 *
	 * @return void
	 */
	public function init_sandbox_session(): void {
		// Only for logged-in admins.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if sandbox cookie exists and validate it.
		if ( isset( $_COOKIE[ self::COOKIE_NAME ] ) && is_string( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			$raw_session_id = $_COOKIE[ self::COOKIE_NAME ];
			
			// Validate cookie format (alphanumeric, reasonable length).
			if ( strlen( $raw_session_id ) > 64 || ! preg_match( '/^[a-zA-Z0-9]+$/', $raw_session_id ) ) {
				// Invalid format, clear cookie.
				$this->clear_sandbox_cookie();
				return;
			}
			
			$session_id = sanitize_key( $raw_session_id );
			
			// Validate session.
			$state = get_transient( self::TRANSIENT_PREFIX . $session_id );
			if ( false === $state ) {
				// Session expired, clear cookie.
				$this->clear_sandbox_cookie();
			}
		}
	}

	/**
	 * Validate and get session ID from cookie.
	 *
	 * @return string|false Session ID if valid, false otherwise.
	 */
	private function get_validated_session_id() {
		if ( ! isset( $_COOKIE[ self::COOKIE_NAME ] ) || ! is_string( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			return false;
		}

		$raw_session_id = $_COOKIE[ self::COOKIE_NAME ];

		// Validate cookie format (alphanumeric, reasonable length).
		if ( strlen( $raw_session_id ) > 64 || ! preg_match( '/^[a-zA-Z0-9]+$/', $raw_session_id ) ) {
			return false;
		}

		return sanitize_key( $raw_session_id );
	}

	/**
	 * Check if current user is in sandbox mode.
	 *
	 * @return bool True if sandbox is active for current user.
	 */
	private function is_sandbox_active(): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$session_id = $this->get_validated_session_id();
		if ( false === $session_id ) {
			return false;
		}

		$state = get_transient( self::TRANSIENT_PREFIX . $session_id );

		return false !== $state && is_array( $state ) && ! empty( $state['active'] );
	}

	/**
	 * Get sandbox state for current user.
	 *
	 * @return array|false Sandbox state or false if not active.
	 */
	private function get_sandbox_state() {
		$session_id = $this->get_validated_session_id();
		if ( false === $session_id ) {
			return false;
		}

		$state = get_transient( self::TRANSIENT_PREFIX . $session_id );

		if ( false === $state || ! is_array( $state ) ) {
			return false;
		}

		return $state;
	}

	/**
	 * Save sandbox state for current user.
	 *
	 * @param array $state Sandbox state to save.
	 * @return bool True on success, false on failure.
	 */
	private function save_sandbox_state( array $state ): bool {
		$session_id = $this->get_or_create_session_id();
		
		// Store state for 24 hours.
		return set_transient( self::TRANSIENT_PREFIX . $session_id, $state, DAY_IN_SECONDS );
	}

	/**
	 * Get or create session ID for current user.
	 *
	 * @return string Session ID.
	 */
	private function get_or_create_session_id(): string {
		$session_id = $this->get_validated_session_id();
		if ( false !== $session_id ) {
			return $session_id;
		}

		// Generate new session ID.
		$session_id = wp_generate_password( 32, false );
		
		// Set cookie (24 hour expiry).
		$this->set_sandbox_cookie( $session_id );

		return $session_id;
	}

	/**
	 * Set sandbox cookie.
	 *
	 * @param string $session_id Session ID.
	 * @return void
	 */
	private function set_sandbox_cookie( string $session_id ): void {
		setcookie(
			self::COOKIE_NAME,
			$session_id,
			time() + DAY_IN_SECONDS,
			COOKIEPATH,
			COOKIE_DOMAIN,
			is_ssl(),
			true // HTTP only.
		);
	}

	/**
	 * Clear sandbox cookie.
	 *
	 * @return void
	 */
	private function clear_sandbox_cookie(): void {
		setcookie(
			self::COOKIE_NAME,
			'',
			time() - 3600,
			COOKIEPATH,
			COOKIE_DOMAIN,
			is_ssl(),
			true
		);
	}

	/**
	 * Filter active plugins for sandbox mode.
	 *
	 * @param array $plugins Active plugins.
	 * @return array Filtered plugins.
	 */
	public function filter_active_plugins( $plugins ): array {
		if ( ! is_array( $plugins ) ) {
			return $plugins;
		}

		$state = $this->get_sandbox_state();
		if ( false === $state || empty( $state['disabled_plugins'] ) ) {
			return $plugins;
		}

		$disabled_plugins = (array) $state['disabled_plugins'];

		// Remove disabled plugins from the list.
		return array_values( array_diff( $plugins, $disabled_plugins ) );
	}

	/**
	 * Filter stylesheet (child theme) for sandbox mode.
	 *
	 * @param mixed $stylesheet Current stylesheet.
	 * @return string Filtered stylesheet.
	 */
	public function filter_stylesheet( $stylesheet ): string {
		// Ensure we have a string to work with.
		if ( ! is_string( $stylesheet ) ) {
			return '';
		}

		$state = $this->get_sandbox_state();
		if ( false === $state || empty( $state['theme'] ) ) {
			return $stylesheet;
		}

		return sanitize_key( $state['theme'] );
	}

	/**
	 * Filter template (parent theme) for sandbox mode.
	 *
	 * @param mixed $template Current template.
	 * @return string Filtered template.
	 */
	public function filter_template( $template ): string {
		// Ensure we have a string to work with.
		if ( ! is_string( $template ) ) {
			return '';
		}

		$state = $this->get_sandbox_state();
		if ( false === $state || empty( $state['theme'] ) ) {
			return $template;
		}

		// Get the theme object to determine parent theme.
		$theme = wp_get_theme( $state['theme'] );
		if ( ! $theme->exists() ) {
			return $template;
		}

		// If theme has a parent, return parent name; otherwise return theme name.
		$parent = $theme->get( 'Template' );
		return ! empty( $parent ) ? sanitize_key( $parent ) : sanitize_key( $state['theme'] );
	}

	/**
	 * Display admin notice when in sandbox mode.
	 *
	 * @return void
	 */
	public function sandbox_mode_notice(): void {
		$state = $this->get_sandbox_state();
		if ( false === $state ) {
			return;
		}

		$disabled_count = ! empty( $state['disabled_plugins'] ) ? count( $state['disabled_plugins'] ) : 0;
		$theme_override = ! empty( $state['theme'] );

		$message = __( '🔬 <strong>Conflict Sandbox Active:</strong> ', 'plugin-wp-support-thisismyurl' );
		
		if ( $disabled_count > 0 ) {
			$message .= sprintf(
				/* translators: %d: number of disabled plugins */
				_n( '%d plugin disabled', '%d plugins disabled', $disabled_count, 'plugin-wp-support-thisismyurl' ),
				$disabled_count
			);
		}

		if ( $theme_override ) {
			if ( $disabled_count > 0 ) {
				$message .= __( ', ', 'plugin-wp-support-thisismyurl' );
			}
			$message .= sprintf(
				/* translators: %s: theme name */
				__( 'theme switched to %s', 'plugin-wp-support-thisismyurl' ),
				esc_html( $state['theme'] )
			);
		}

		$message .= sprintf(
			' <a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=wp-support-conflict-sandbox' ) ),
			__( 'Manage Sandbox', 'plugin-wp-support-thisismyurl' )
		);

		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			wp_kses_post( $message )
		);
	}

	/**
	 * AJAX handler: Toggle sandbox on/off.
	 *
	 * @return void
	 */
	public function ajax_toggle_sandbox(): void {
		check_ajax_referer( 'wps_sandbox_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$enable = ! empty( $_POST['enable'] );

		if ( $enable ) {
			// Enable sandbox mode.
			$state = array(
				'active'           => true,
				'disabled_plugins' => array(),
				'theme'            => '',
			);
			$this->save_sandbox_state( $state );
			wp_send_json_success( array( 'message' => __( 'Sandbox mode enabled', 'plugin-wp-support-thisismyurl' ) ) );
		} else {
			// Disable sandbox mode.
			$session_id = $this->get_or_create_session_id();
			delete_transient( self::TRANSIENT_PREFIX . $session_id );
			$this->clear_sandbox_cookie();
			wp_send_json_success( array( 'message' => __( 'Sandbox mode disabled', 'plugin-wp-support-thisismyurl' ) ) );
		}
	}

	/**
	 * AJAX handler: Toggle plugin in sandbox.
	 *
	 * @return void
	 */
	public function ajax_toggle_plugin(): void {
		check_ajax_referer( 'wps_sandbox_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$plugin = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : '';
		$disable = ! empty( $_POST['disable'] );

		if ( empty( $plugin ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid plugin', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$state = $this->get_sandbox_state();
		if ( false === $state ) {
			$state = array(
				'active'           => true,
				'disabled_plugins' => array(),
				'theme'            => '',
			);
		}

		if ( ! isset( $state['disabled_plugins'] ) ) {
			$state['disabled_plugins'] = array();
		}

		if ( $disable ) {
			// Add plugin to disabled list.
			if ( ! in_array( $plugin, $state['disabled_plugins'], true ) ) {
				$state['disabled_plugins'][] = $plugin;
			}
		} else {
			// Remove plugin from disabled list.
			$state['disabled_plugins'] = array_diff( $state['disabled_plugins'], array( $plugin ) );
		}

		$this->save_sandbox_state( $state );
		wp_send_json_success( array( 'message' => __( 'Plugin status updated', 'plugin-wp-support-thisismyurl' ) ) );
	}

	/**
	 * AJAX handler: Switch theme in sandbox.
	 *
	 * @return void
	 */
	public function ajax_switch_theme(): void {
		check_ajax_referer( 'wps_sandbox_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$theme = isset( $_POST['theme'] ) ? sanitize_key( $_POST['theme'] ) : '';

		$state = $this->get_sandbox_state();
		if ( false === $state ) {
			$state = array(
				'active'           => true,
				'disabled_plugins' => array(),
				'theme'            => '',
			);
		}

		// Validate theme exists.
		if ( ! empty( $theme ) ) {
			$theme_obj = wp_get_theme( $theme );
			if ( ! $theme_obj->exists() ) {
				wp_send_json_error( array( 'message' => __( 'Invalid theme', 'plugin-wp-support-thisismyurl' ) ) );
			}
		}

		$state['theme'] = $theme;
		$this->save_sandbox_state( $state );

		wp_send_json_success( array( 
			'message' => empty( $theme ) 
				? __( 'Theme override cleared', 'plugin-wp-support-thisismyurl' )
				: __( 'Theme switched', 'plugin-wp-support-thisismyurl' ),
		) );
	}

	/**
	 * AJAX handler: Clear sandbox state.
	 *
	 * @return void
	 */
	public function ajax_clear_sandbox(): void {
		check_ajax_referer( 'wps_sandbox_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$state = array(
			'active'           => true,
			'disabled_plugins' => array(),
			'theme'            => '',
		);
		$this->save_sandbox_state( $state );

		wp_send_json_success( array( 'message' => __( 'Sandbox cleared', 'plugin-wp-support-thisismyurl' ) ) );
	}

	/**
	 * Render admin page for sandbox controls.
	 *
	 * @return void
	 */
	public function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
		}

		$is_active = $this->is_sandbox_active();
		$state     = $this->get_sandbox_state();
		$disabled_plugins = $is_active && $state ? ( $state['disabled_plugins'] ?? array() ) : array();
		$sandbox_theme = $is_active && $state ? ( $state['theme'] ?? '' ) : '';

		// Get all plugins.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		// Get all themes.
		$all_themes     = wp_get_themes();
		$current_theme  = wp_get_theme();

		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Conflict Sandbox', 'plugin-wp-support-thisismyurl' ); ?></h1>
			
			<div class="card">
				<h2><?php esc_html_e( 'About Conflict Sandbox', 'plugin-wp-support-thisismyurl' ); ?></h2>
				<p><?php esc_html_e( 'Conflict Sandbox allows you to deactivate plugins and switch themes only for your current browser session. Visitors will continue to see the site normally while you debug conflicts in real-time.', 'plugin-wp-support-thisismyurl' ); ?></p>
				<p><strong><?php esc_html_e( 'Important:', 'plugin-wp-support-thisismyurl' ); ?></strong> <?php esc_html_e( 'Changes made in the sandbox are temporary and only affect your view. No actual plugins are deactivated or themes switched for the live site.', 'plugin-wp-support-thisismyurl' ); ?></p>
			</div>

			<div class="card" style="margin-top: 20px;">
				<h2><?php esc_html_e( 'Sandbox Status', 'plugin-wp-support-thisismyurl' ); ?></h2>
				<p>
					<?php if ( $is_active ) : ?>
						<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
						<strong><?php esc_html_e( 'Sandbox Mode: Active', 'plugin-wp-support-thisismyurl' ); ?></strong>
					<?php else : ?>
						<span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span>
						<strong><?php esc_html_e( 'Sandbox Mode: Inactive', 'plugin-wp-support-thisismyurl' ); ?></strong>
					<?php endif; ?>
				</p>
				<p>
					<button type="button" class="button button-primary" id="wps-toggle-sandbox">
						<?php echo $is_active ? esc_html__( 'Disable Sandbox', 'plugin-wp-support-thisismyurl' ) : esc_html__( 'Enable Sandbox', 'plugin-wp-support-thisismyurl' ); ?>
					</button>
					<?php if ( $is_active ) : ?>
						<button type="button" class="button" id="wps-clear-sandbox">
							<?php esc_html_e( 'Clear All Overrides', 'plugin-wp-support-thisismyurl' ); ?>
						</button>
					<?php endif; ?>
				</p>
			</div>

			<?php if ( $is_active ) : ?>
				<div class="card" style="margin-top: 20px;">
					<h2><?php esc_html_e( 'Plugin Management', 'plugin-wp-support-thisismyurl' ); ?></h2>
					<p><?php esc_html_e( 'Toggle plugins on/off in your sandbox. This does not affect the live site.', 'plugin-wp-support-thisismyurl' ); ?></p>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Plugin', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Live Status', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Sandbox Status', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Action', 'plugin-wp-support-thisismyurl' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $all_plugins as $plugin_file => $plugin_data ) : 
								$is_active_live = in_array( $plugin_file, $active_plugins, true );
								$is_disabled_sandbox = in_array( $plugin_file, $disabled_plugins, true );
								$is_active_sandbox = $is_active_live && ! $is_disabled_sandbox;
							?>
								<tr>
									<td><strong><?php echo esc_html( $plugin_data['Name'] ); ?></strong></td>
									<td>
										<?php if ( $is_active_live ) : ?>
											<span class="dashicons dashicons-yes" style="color: #46b450;"></span>
											<?php esc_html_e( 'Active', 'plugin-wp-support-thisismyurl' ); ?>
										<?php else : ?>
											<span class="dashicons dashicons-minus" style="color: #999;"></span>
											<?php esc_html_e( 'Inactive', 'plugin-wp-support-thisismyurl' ); ?>
										<?php endif; ?>
									</td>
									<td>
										<?php if ( $is_active_sandbox ) : ?>
											<span class="dashicons dashicons-yes" style="color: #46b450;"></span>
											<?php esc_html_e( 'Active', 'plugin-wp-support-thisismyurl' ); ?>
										<?php else : ?>
											<span class="dashicons dashicons-minus" style="color: #999;"></span>
											<?php esc_html_e( 'Inactive', 'plugin-wp-support-thisismyurl' ); ?>
										<?php endif; ?>
									</td>
									<td>
										<?php if ( $is_active_live ) : ?>
											<button type="button" 
												class="button button-small wps-toggle-plugin" 
												data-plugin="<?php echo esc_attr( $plugin_file ); ?>"
												data-action="<?php echo $is_disabled_sandbox ? 'enable' : 'disable'; ?>">
												<?php echo $is_disabled_sandbox ? esc_html__( 'Enable in Sandbox', 'plugin-wp-support-thisismyurl' ) : esc_html__( 'Disable in Sandbox', 'plugin-wp-support-thisismyurl' ); ?>
											</button>
										<?php else : ?>
											<span style="color: #999;"><?php esc_html_e( 'N/A', 'plugin-wp-support-thisismyurl' ); ?></span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<div class="card" style="margin-top: 20px;">
					<h2><?php esc_html_e( 'Theme Override', 'plugin-wp-support-thisismyurl' ); ?></h2>
					<p><?php esc_html_e( 'Switch to a different theme in your sandbox to test conflicts.', 'plugin-wp-support-thisismyurl' ); ?></p>
					<p>
						<strong><?php esc_html_e( 'Current Live Theme:', 'plugin-wp-support-thisismyurl' ); ?></strong>
						<?php echo esc_html( $current_theme->get( 'Name' ) ); ?>
					</p>
					<?php if ( ! empty( $sandbox_theme ) ) : ?>
						<p>
							<strong><?php esc_html_e( 'Sandbox Theme:', 'plugin-wp-support-thisismyurl' ); ?></strong>
							<?php 
							$sandbox_theme_obj = wp_get_theme( $sandbox_theme );
							echo esc_html( $sandbox_theme_obj->get( 'Name' ) ); 
							?>
						</p>
					<?php endif; ?>
					<p>
						<label for="wps-theme-select"><?php esc_html_e( 'Switch to theme:', 'plugin-wp-support-thisismyurl' ); ?></label>
						<select id="wps-theme-select" class="regular-text">
							<option value=""><?php esc_html_e( '-- Use Live Theme --', 'plugin-wp-support-thisismyurl' ); ?></option>
							<?php foreach ( $all_themes as $theme_slug => $theme_obj ) : ?>
								<option value="<?php echo esc_attr( $theme_slug ); ?>" <?php selected( $sandbox_theme, $theme_slug ); ?>>
									<?php echo esc_html( $theme_obj->get( 'Name' ) ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<button type="button" class="button" id="wps-switch-theme">
							<?php esc_html_e( 'Apply Theme', 'plugin-wp-support-thisismyurl' ); ?>
						</button>
					</p>
				</div>
			<?php endif; ?>
		</div>

		<script type="text/javascript">
		jQuery(document).ready(function($) {
			var nonce = '<?php echo esc_js( wp_create_nonce( 'wps_sandbox_nonce' ) ); ?>';

			// Toggle sandbox on/off.
			$('#wps-toggle-sandbox').on('click', function() {
				var isActive = <?php echo $is_active ? 'true' : 'false'; ?>;
				var button = $(this);
				button.prop('disabled', true);

				$.post(ajaxurl, {
					action: 'wps_sandbox_toggle',
					nonce: nonce,
					enable: !isActive
				}, function(response) {
					if (response.success) {
						location.reload();
					} else {
						alert(response.data.message || '<?php echo esc_js( __( 'Error toggling sandbox', 'plugin-wp-support-thisismyurl' ) ); ?>');
						button.prop('disabled', false);
					}
				});
			});

			// Clear sandbox.
			$('#wps-clear-sandbox').on('click', function() {
				if (!confirm('<?php echo esc_js( __( 'Are you sure you want to clear all sandbox overrides?', 'plugin-wp-support-thisismyurl' ) ); ?>')) {
					return;
				}

				var button = $(this);
				button.prop('disabled', true);

				$.post(ajaxurl, {
					action: 'wps_sandbox_clear',
					nonce: nonce
				}, function(response) {
					if (response.success) {
						location.reload();
					} else {
						alert(response.data.message || '<?php echo esc_js( __( 'Error clearing sandbox', 'plugin-wp-support-thisismyurl' ) ); ?>');
						button.prop('disabled', false);
					}
				});
			});

			// Toggle plugin in sandbox.
			$('.wps-toggle-plugin').on('click', function() {
				var button = $(this);
				var plugin = button.data('plugin');
				var action = button.data('action');
				button.prop('disabled', true);

				$.post(ajaxurl, {
					action: 'wps_sandbox_toggle_plugin',
					nonce: nonce,
					plugin: plugin,
					disable: action === 'disable'
				}, function(response) {
					if (response.success) {
						location.reload();
					} else {
						alert(response.data.message || '<?php echo esc_js( __( 'Error toggling plugin', 'plugin-wp-support-thisismyurl' ) ); ?>');
						button.prop('disabled', false);
					}
				});
			});

			// Switch theme in sandbox.
			$('#wps-switch-theme').on('click', function() {
				var button = $(this);
				var theme = $('#wps-theme-select').val();
				button.prop('disabled', true);

				$.post(ajaxurl, {
					action: 'wps_sandbox_switch_theme',
					nonce: nonce,
					theme: theme
				}, function(response) {
					if (response.success) {
						location.reload();
					} else {
						alert(response.data.message || '<?php echo esc_js( __( 'Error switching theme', 'plugin-wp-support-thisismyurl' ) ); ?>');
						button.prop('disabled', false);
					}
				});
			});
		});
		</script>

		<style>
		.card {
			padding: 20px;
			background: #fff;
			border: 1px solid #ccd0d4;
			box-shadow: 0 1px 1px rgba(0,0,0,.04);
		}
		.card h2 {
			margin-top: 0;
		}
		</style>
		<?php
	}
}
