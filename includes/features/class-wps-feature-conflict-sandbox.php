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
 * Feature: Conflict Sandbox
 *
 * Provides per-user "Conflict Isolation Mode" that allows administrators to:
 * - Deactivate plugins only for their current browser session
 * - Switch themes only for their current browser session
 * - Debug conflicts in real-time without affecting visitors or other admins
 *
 * Uses secure cookie-based session tracking to isolate changes per-user.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73002
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPS_Feature_Conflict_Sandbox
 *
 * Per-user conflict isolation implementation.
/**
 * WPS_Feature_Conflict_Sandbox
 *
 * Per-user conflict debugging sandbox implementation.
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
	private const SANDBOX_COOKIE = 'wps_conflict_sandbox';

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
				'id'                  => 'conflict-sandbox',
				'name'                => __( 'Conflict Sandbox', 'plugin-wp-support-thisismyurl' ),
				'description'         => __( 'Per-user plugin deactivation and theme switching for debugging without affecting visitors', 'plugin-wp-support-thisismyurl' ),
				'description'         => __( 'Per-user conflict isolation: deactivate plugins or switch themes only for your session while visitors see the normal site', 'plugin-wp-support-thisismyurl' ),
				'scope'               => 'core',
				'default_enabled'     => false,
				'version'             => '1.0.0',
				'widget_group'        => 'debugging',
				'widget_label'        => __( 'Debugging & Diagnostics', 'plugin-wp-support-thisismyurl' ),
				'widget_description'  => __( 'Advanced debugging features for troubleshooting', 'plugin-wp-support-thisismyurl' ),
				'widget_description'  => __( 'Tools for diagnosing and resolving site conflicts', 'plugin-wp-support-thisismyurl' ),
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
	private function get_validated_session_id(): string|false {
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
	private function get_sandbox_state(): array|false {
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
	 * @param mixed $plugins Active plugins.
	 * @return array Filtered plugins.
	 */
	public function filter_active_plugins( $plugins ): array {
		if ( ! is_array( $plugins ) ) {
			return array();
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
		// Early hook to filter plugins before WordPress loads them.
		add_filter( 'option_active_plugins', array( $this, 'filter_active_plugins' ), 1 );
		add_filter( 'site_option_active_sitewide_plugins', array( $this, 'filter_network_plugins' ), 1 );

		// Filter theme to apply per-user override.
		add_filter( 'stylesheet', array( $this, 'filter_stylesheet' ), 1 );
		add_filter( 'template', array( $this, 'filter_template' ), 1 );

		// Admin interface hooks.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_indicator' ), 100 );

		// AJAX handlers for sandbox control.
		add_action( 'wp_ajax_wps_sandbox_toggle_plugin', array( $this, 'ajax_toggle_plugin' ) );
		add_action( 'wp_ajax_wps_sandbox_set_theme', array( $this, 'ajax_set_theme' ) );
		add_action( 'wp_ajax_wps_sandbox_exit', array( $this, 'ajax_exit_sandbox' ) );
		add_action( 'wp_ajax_wps_sandbox_enter', array( $this, 'ajax_enter_sandbox' ) );
	}

	/**
	 * Check if current user is in sandbox mode.
	 *
	 * @return bool True if in sandbox mode.
	 */
	private function is_sandbox_active(): bool {
		// Only admins can use sandbox mode.
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// Check for sandbox cookie.
		$cookie = isset( $_COOKIE[ self::SANDBOX_COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ self::SANDBOX_COOKIE ] ) ) : '';
		if ( empty( $cookie ) ) {
			return false;
		}

		// Verify cookie signature.
		return $this->verify_sandbox_cookie( $cookie );
	}

	/**
	 * Get sandbox configuration from cookie.
	 *
	 * @return array{disabled_plugins: array<string>, theme: string}
	 */
	private function get_sandbox_config(): array {
		$default = array(
			'disabled_plugins' => array(),
			'theme'            => '',
		);

		if ( ! $this->is_sandbox_active() ) {
			return $default;
		}

		$cookie = isset( $_COOKIE[ self::SANDBOX_COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ self::SANDBOX_COOKIE ] ) ) : '';
		if ( empty( $cookie ) ) {
			return $default;
		}

		// Decode cookie data.
		$data = $this->decode_sandbox_cookie( $cookie );
		if ( ! $data ) {
			return $default;
		}

		return array(
			'disabled_plugins' => isset( $data['disabled_plugins'] ) && is_array( $data['disabled_plugins'] ) ? $data['disabled_plugins'] : array(),
			'theme'            => isset( $data['theme'] ) && is_string( $data['theme'] ) ? $data['theme'] : '',
		);
	}

	/**
	 * Filter active plugins to exclude disabled ones in sandbox.
	 *
	 * @param mixed $plugins Active plugins list.
	 * @return mixed Modified plugins list.
	 */
	public function filter_active_plugins( $plugins ) {
		if ( ! $this->is_sandbox_active() || ! is_array( $plugins ) ) {
			return $plugins;
		}

		$config = $this->get_sandbox_config();
		if ( empty( $config['disabled_plugins'] ) ) {
			return $plugins;
		}

		// Filter out disabled plugins.
		$filtered = array_filter(
			$plugins,
			function ( $plugin ) use ( $config ) {
				return ! in_array( $plugin, $config['disabled_plugins'], true );
			}
		);

		return array_values( $filtered );
	}

	/**
	 * Filter network-active plugins in sandbox mode.
	 *
	 * @param mixed $plugins Network active plugins.
	 * @return mixed Modified plugins list.
	 */
	public function filter_network_plugins( $plugins ) {
		if ( ! $this->is_sandbox_active() || ! is_array( $plugins ) ) {
			return $plugins;
		}

		$config = $this->get_sandbox_config();
		if ( empty( $config['disabled_plugins'] ) ) {
			return $plugins;
		}

		// Filter out disabled network plugins.
		foreach ( $config['disabled_plugins'] as $disabled ) {
			if ( isset( $plugins[ $disabled ] ) ) {
				unset( $plugins[ $disabled ] );
			}
		}

		return $plugins;
	}

	/**
	 * Filter stylesheet (child theme) in sandbox mode.
	 *
	 * @param string $stylesheet Current stylesheet.
	 * @return string Modified stylesheet.
	 */
	public function filter_stylesheet( string $stylesheet ): string {
		if ( ! $this->is_sandbox_active() ) {
			return $stylesheet;
		}

		$config = $this->get_sandbox_config();
		if ( empty( $config['theme'] ) ) {
			return $stylesheet;
		}

		return $config['theme'];
	}

	/**
	 * Filter template (parent theme) in sandbox mode.
	 *
	 * @param string $template Current template.
	 * @return string Modified template.
	 */
	public function filter_template( string $template ): string {
		if ( ! $this->is_sandbox_active() ) {
			return $template;
		}

		$config = $this->get_sandbox_config();
		if ( empty( $config['theme'] ) ) {
			return $template;
		}

		// Get theme object to determine if it has a parent.
		$theme = wp_get_theme( $config['theme'] );
		if ( ! $theme->exists() ) {
			return $template;
		}

		// Return parent theme if exists, otherwise return theme itself.
		$parent = $theme->get_template();
		return ! empty( $parent ) ? $parent : $config['theme'];
	}

	/**
	 * Add admin menu for sandbox control panel.
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		add_submenu_page(
			'wp-support',
			__( 'Conflict Sandbox', 'plugin-wp-support-thisismyurl' ),
			__( 'Conflict Sandbox', 'plugin-wp-support-thisismyurl' ),
			'manage_options',
			'wps-conflict-sandbox',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Render admin control panel.
	 *
	 * @return void
	 */
	public function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
		}

		$is_active = $this->is_sandbox_active();
		$config    = $this->get_sandbox_config();

		// Get all plugins.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins    = get_plugins();
		$active_plugins = (array) get_option( 'active_plugins', array() );

		// Get all themes.
		$all_themes     = wp_get_themes();
		$current_theme  = get_stylesheet();

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Conflict Sandbox', 'plugin-wp-support-thisismyurl' ); ?></h1>

			<?php if ( $is_active ) : ?>
				<div class="notice notice-warning" style="padding: 15px; margin-bottom: 20px; border-left: 4px solid #ffb900;">
					<p style="margin: 0; font-weight: bold; font-size: 14px;">
						<?php esc_html_e( '⚠️ SANDBOX MODE ACTIVE', 'plugin-wp-support-thisismyurl' ); ?>
					</p>
					<p style="margin: 5px 0 0 0;">
						<?php esc_html_e( 'You are viewing the site with your custom configuration. Visitors see the normal site.', 'plugin-wp-support-thisismyurl' ); ?>
					</p>
					<p style="margin: 10px 0 0 0;">
						<button type="button" class="button button-primary" id="wps-exit-sandbox">
							<?php esc_html_e( 'Exit Sandbox Mode', 'plugin-wp-support-thisismyurl' ); ?>
						</button>
					</p>
				</div>
			<?php else : ?>
				<div class="notice notice-info" style="padding: 15px; margin-bottom: 20px;">
					<p style="margin: 0;">
						<?php esc_html_e( 'Sandbox mode allows you to deactivate plugins or switch themes for your session only. Visitors will continue to see the normal site.', 'plugin-wp-support-thisismyurl' ); ?>
					</p>
					<p style="margin: 10px 0 0 0;">
						<button type="button" class="button button-primary" id="wps-enter-sandbox">
							<?php esc_html_e( 'Enter Sandbox Mode', 'plugin-wp-support-thisismyurl' ); ?>
						</button>
					</p>
				</div>
			<?php endif; ?>

			<div id="wps-sandbox-controls" style="<?php echo $is_active ? '' : 'display:none;'; ?>">
				<div class="card" style="max-width: 800px;">
					<h2><?php esc_html_e( 'Plugin Control', 'plugin-wp-support-thisismyurl' ); ?></h2>
					<p><?php esc_html_e( 'Disable plugins for your session only. Changes do not affect visitors or the actual plugin configuration.', 'plugin-wp-support-thisismyurl' ); ?></p>
					
					<table class="widefat striped" style="margin-top: 15px;">
						<thead>
							<tr>
								<th style="width: 60px;"><?php esc_html_e( 'Status', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Plugin', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th style="width: 150px;"><?php esc_html_e( 'Action', 'plugin-wp-support-thisismyurl' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $all_plugins as $plugin_file => $plugin_data ) : ?>
								<?php
								$is_active_normally   = in_array( $plugin_file, $active_plugins, true );
								$is_disabled_sandbox  = in_array( $plugin_file, $config['disabled_plugins'], true );
								$effective_status     = $is_active_normally && ! $is_disabled_sandbox;
								$is_this_plugin       = strpos( $plugin_file, 'wp-support-thisismyurl.php' ) !== false;
								?>
								<tr>
									<td>
										<?php if ( $is_this_plugin ) : ?>
											<span style="color: #999;">
												<?php esc_html_e( 'Protected', 'plugin-wp-support-thisismyurl' ); ?>
											</span>
										<?php elseif ( $effective_status ) : ?>
											<span style="color: #46b450;">●</span> <?php esc_html_e( 'Active', 'plugin-wp-support-thisismyurl' ); ?>
										<?php else : ?>
											<span style="color: #dc3232;">●</span> <?php esc_html_e( 'Inactive', 'plugin-wp-support-thisismyurl' ); ?>
										<?php endif; ?>
									</td>
									<td>
										<strong><?php echo esc_html( $plugin_data['Name'] ); ?></strong>
										<?php if ( ! empty( $plugin_data['Version'] ) ) : ?>
											<span style="color: #666;"><?php echo esc_html( $plugin_data['Version'] ); ?></span>
										<?php endif; ?>
										<?php if ( $is_disabled_sandbox ) : ?>
											<span style="color: #ffb900; margin-left: 10px;">
												<?php esc_html_e( '(Disabled in Sandbox)', 'plugin-wp-support-thisismyurl' ); ?>
											</span>
										<?php endif; ?>
									</td>
									<td>
										<?php if ( $is_this_plugin ) : ?>
											<em style="color: #999;"><?php esc_html_e( 'Cannot disable', 'plugin-wp-support-thisismyurl' ); ?></em>
										<?php elseif ( $is_active_normally ) : ?>
											<button type="button" 
												class="button button-small wps-toggle-plugin" 
												data-plugin="<?php echo esc_attr( $plugin_file ); ?>"
												data-action="<?php echo $is_disabled_sandbox ? 'enable' : 'disable'; ?>">
												<?php echo $is_disabled_sandbox ? esc_html__( 'Enable', 'plugin-wp-support-thisismyurl' ) : esc_html__( 'Disable', 'plugin-wp-support-thisismyurl' ); ?>
											</button>
										<?php else : ?>
											<em style="color: #999;"><?php esc_html_e( 'Already inactive', 'plugin-wp-support-thisismyurl' ); ?></em>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<div class="card" style="max-width: 800px; margin-top: 20px;">
					<h2><?php esc_html_e( 'Theme Control', 'plugin-wp-support-thisismyurl' ); ?></h2>
					<p><?php esc_html_e( 'Switch themes for your session only. Changes do not affect visitors or the actual theme configuration.', 'plugin-wp-support-thisismyurl' ); ?></p>
					
					<table class="widefat striped" style="margin-top: 15px;">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Theme', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th style="width: 150px;"><?php esc_html_e( 'Action', 'plugin-wp-support-thisismyurl' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $all_themes as $theme_slug => $theme ) : ?>
								<?php
								$is_current_in_sandbox = ! empty( $config['theme'] ) && $config['theme'] === $theme_slug;
								$is_current_normally   = $current_theme === $theme_slug;
								?>
								<tr>
									<td>
										<strong><?php echo esc_html( $theme->get( 'Name' ) ); ?></strong>
										<?php if ( $theme->get( 'Version' ) ) : ?>
											<span style="color: #666;"><?php echo esc_html( $theme->get( 'Version' ) ); ?></span>
										<?php endif; ?>
										<?php if ( $is_current_in_sandbox ) : ?>
											<span style="color: #46b450; margin-left: 10px;">
												<?php esc_html_e( '(Active in Sandbox)', 'plugin-wp-support-thisismyurl' ); ?>
											</span>
										<?php elseif ( $is_current_normally && empty( $config['theme'] ) ) : ?>
											<span style="color: #46b450; margin-left: 10px;">
												<?php esc_html_e( '(Currently Active)', 'plugin-wp-support-thisismyurl' ); ?>
											</span>
										<?php endif; ?>
									</td>
									<td>
										<?php if ( $is_current_in_sandbox ) : ?>
											<button type="button" 
												class="button button-small wps-set-theme" 
												data-theme="">
												<?php esc_html_e( 'Reset to Normal', 'plugin-wp-support-thisismyurl' ); ?>
											</button>
										<?php elseif ( $is_current_normally && empty( $config['theme'] ) ) : ?>
											<em style="color: #666;"><?php esc_html_e( 'Active', 'plugin-wp-support-thisismyurl' ); ?></em>
										<?php else : ?>
											<button type="button" 
												class="button button-small wps-set-theme" 
												data-theme="<?php echo esc_attr( $theme_slug ); ?>">
												<?php esc_html_e( 'Activate in Sandbox', 'plugin-wp-support-thisismyurl' ); ?>
											</button>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_assets( string $hook ): void {
		// Only on sandbox page.
		if ( 'support_page_wps-conflict-sandbox' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'wps-conflict-sandbox',
			plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/css/conflict-sandbox.css',
			array(),
			'1.0.0'
		);

		wp_enqueue_script(
			'wps-conflict-sandbox',
			plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/js/conflict-sandbox.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'wps-conflict-sandbox',
			'wpsSandbox',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wps_sandbox' ),
				'strings' => array(
					'entering'       => __( 'Entering sandbox mode...', 'plugin-wp-support-thisismyurl' ),
					'exiting'        => __( 'Exiting sandbox mode...', 'plugin-wp-support-thisismyurl' ),
					'toggling'       => __( 'Updating...', 'plugin-wp-support-thisismyurl' ),
					'settingTheme'   => __( 'Switching theme...', 'plugin-wp-support-thisismyurl' ),
					'error'          => __( 'An error occurred. Please try again.', 'plugin-wp-support-thisismyurl' ),
					'confirmExit'    => __( 'Are you sure you want to exit sandbox mode? All your sandbox changes will be discarded.', 'plugin-wp-support-thisismyurl' ),
				),
			)
		);
	}

	/**
	 * Add admin bar indicator when in sandbox mode.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public function add_admin_bar_indicator( $wp_admin_bar ): void {
		if ( ! $this->is_sandbox_active() ) {
			return;
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'wps-sandbox-indicator',
				'title' => '<span style="color: #ffb900;">⚠️ ' . esc_html__( 'Sandbox Mode', 'plugin-wp-support-thisismyurl' ) . '</span>',
				'href'  => admin_url( 'admin.php?page=wps-conflict-sandbox' ),
				'meta'  => array(
					'title' => __( 'You are in Conflict Sandbox mode. Click to manage.', 'plugin-wp-support-thisismyurl' ),
				),
			)
		);
	}

	/**
	 * AJAX handler to toggle plugin in sandbox.
	 *
	 * @return void
	 */
	public function ajax_toggle_plugin(): void {
		check_ajax_referer( 'wps_sandbox', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$plugin = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : '';
		$action = isset( $_POST['action_type'] ) ? sanitize_text_field( wp_unslash( $_POST['action_type'] ) ) : '';

		if ( empty( $plugin ) || empty( $action ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters.', 'plugin-wp-support-thisismyurl' ) ) );
		}

		// Prevent disabling this plugin.
		if ( strpos( $plugin, 'wp-support-thisismyurl.php' ) !== false ) {
			wp_send_json_error( array( 'message' => __( 'Cannot disable the WP Support plugin.', 'plugin-wp-support-thisismyurl' ) ) );
		}

		// Get current config.
		$config = $this->get_sandbox_config();

		// Update disabled plugins list.
		if ( 'disable' === $action ) {
			if ( ! in_array( $plugin, $config['disabled_plugins'], true ) ) {
				$config['disabled_plugins'][] = $plugin;
			}
		} else {
			$config['disabled_plugins'] = array_values(
				array_filter(
					$config['disabled_plugins'],
					function ( $p ) use ( $plugin ) {
						return $p !== $plugin;
					}
				)
			);
		}

		// Save updated config.
		$this->set_sandbox_cookie( $config );

		wp_send_json_success(
			array(
				'message' => 'disable' === $action
					? __( 'Plugin disabled in sandbox.', 'plugin-wp-support-thisismyurl' )
					: __( 'Plugin enabled in sandbox.', 'plugin-wp-support-thisismyurl' ),
			)
		);
	}

	/**
	 * AJAX handler to set theme in sandbox.
	 *
	 * @return void
	 */
	public function ajax_set_theme(): void {
		check_ajax_referer( 'wps_sandbox', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$theme = isset( $_POST['theme'] ) ? sanitize_text_field( wp_unslash( $_POST['theme'] ) ) : '';

		// Get current config.
		$config         = $this->get_sandbox_config();
		$config['theme'] = $theme;

		// Save updated config.
		$this->set_sandbox_cookie( $config );

		wp_send_json_success(
			array(
				'message' => empty( $theme )
					? __( 'Theme reset to normal.', 'plugin-wp-support-thisismyurl' )
					: __( 'Theme activated in sandbox.', 'plugin-wp-support-thisismyurl' ),
			)
		);
	}

	/**
	 * AJAX handler to exit sandbox mode.
	 *
	 * @return void
	 */
	public function ajax_exit_sandbox(): void {
		check_ajax_referer( 'wps_sandbox', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ) );
		}

		// Clear sandbox cookie.
		$this->clear_sandbox_cookie();

		wp_send_json_success(
			array(
				'message' => __( 'Exited sandbox mode.', 'plugin-wp-support-thisismyurl' ),
			)
		);
	}

	/**
	 * AJAX handler to enter sandbox mode.
	 *
	 * @return void
	 */
	public function ajax_enter_sandbox(): void {
		check_ajax_referer( 'wps_sandbox', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ) );
		}

		// Initialize sandbox with empty config.
		$this->set_sandbox_cookie(
			array(
				'disabled_plugins' => array(),
				'theme'            => '',
			)
		);

		wp_send_json_success(
			array(
				'message' => __( 'Entered sandbox mode.', 'plugin-wp-support-thisismyurl' ),
			)
		);
	}

	/**
	 * Encode and sign sandbox cookie data.
	 *
	 * @param array $config Sandbox configuration.
	 * @return string Encoded cookie value.
	 */
	private function encode_sandbox_cookie( array $config ): string {
		$data = wp_json_encode( $config );
		if ( false === $data ) {
			return '';
		}

		// Create signature.
		$signature = hash_hmac( 'sha256', $data, wp_salt( 'auth' ) );

		// Combine data and signature.
		return base64_encode( $data . '|' . $signature );
	}

	/**
	 * Decode and verify sandbox cookie data.
	 *
	 * @param string $cookie Cookie value.
	 * @return array|false Decoded configuration or false on failure.
	 */
	private function decode_sandbox_cookie( string $cookie ) {
		$decoded = base64_decode( $cookie );
		if ( false === $decoded ) {
			return false;
		}

		// Split data and signature.
		$parts = explode( '|', $decoded, 2 );
		if ( count( $parts ) !== 2 ) {
			return false;
		}

		list( $data, $signature ) = $parts;

		// Verify signature.
		$expected_signature = hash_hmac( 'sha256', $data, wp_salt( 'auth' ) );
		if ( ! hash_equals( $expected_signature, $signature ) ) {
			return false;
		}

		// Decode JSON.
		$config = json_decode( $data, true );
		if ( ! is_array( $config ) ) {
			return false;
		}

		return $config;
	}

	/**
	 * Verify sandbox cookie signature.
	 *
	 * @param string $cookie Cookie value.
	 * @return bool True if valid.
	 */
	private function verify_sandbox_cookie( string $cookie ): bool {
		return false !== $this->decode_sandbox_cookie( $cookie );
	}

	/**
	 * Set sandbox cookie with configuration.
	 *
	 * @param array $config Sandbox configuration.
	 * @return void
	 */
	private function set_sandbox_cookie( array $config ): void {
		$cookie = $this->encode_sandbox_cookie( $config );
		if ( empty( $cookie ) ) {
			return;
		}

		$expiry = time() + self::COOKIE_LIFETIME;

		// Set secure cookie.
		setcookie(
			self::SANDBOX_COOKIE,
			$cookie,
			array(
				'expires'  => $expiry,
				'path'     => COOKIEPATH,
				'domain'   => COOKIE_DOMAIN,
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}

	/**
	 * Clear sandbox cookie.
	 *
	 * @return void
	 */
	private function clear_sandbox_cookie(): void {
		setcookie(
			self::SANDBOX_COOKIE,
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
	}
}
