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
	private const SANDBOX_COOKIE   = 'wps_conflict_sandbox';

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
				'id'                 => 'conflict-sandbox',
				'name'               => __( 'Conflict Sandbox', 'plugin-wp-support-thisismyurl' ),
				'description'        => __( 'Per-user plugin deactivation and theme switching for debugging without affecting visitors', 'plugin-wp-support-thisismyurl' ),
				'description'        => __( 'Per-user conflict isolation: deactivate plugins or switch themes only for your session while visitors see the normal site', 'plugin-wp-support-thisismyurl' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'debugging',
				'widget_label'       => __( 'Debugging & Diagnostics', 'plugin-wp-support-thisismyurl' ),
				'widget_description' => __( 'Advanced debugging features for troubleshooting', 'plugin-wp-support-thisismyurl' ),
				'widget_description' => __( 'Tools for diagnosing and resolving site conflicts', 'plugin-wp-support-thisismyurl' ),
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
		\WPS\CoreSupport\wps_verify_ajax_request( 'wps_sandbox_nonce' );

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
		\WPS\CoreSupport\wps_verify_ajax_request( 'wps_sandbox_nonce' );

		$plugin  = \WPS\CoreSupport\wps_get_post_text( 'plugin' );
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
		\WPS\CoreSupport\wps_verify_ajax_request( 'wps_sandbox_nonce' );

		$theme = \WPS\CoreSupport\wps_get_post_key( 'theme' );

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

		wp_send_json_success(
			array(
				'message' => empty( $theme )
					? __( 'Theme override cleared', 'plugin-wp-support-thisismyurl' )
					: __( 'Theme switched', 'plugin-wp-support-thisismyurl' ),
			)
		);
	}

	/**
	 * AJAX handler: Clear sandbox state.
	 *
	 * @return void
	 */
	public function ajax_clear_sandbox(): void {
		\WPS\CoreSupport\wps_verify_ajax_request( 'wps_sandbox_nonce' );

		$state = array(
			'active'           => true,
			'disabled_plugins' => array(),
			'theme'            => '',
		);
		$this->save_sandbox_state( $state );

		wp_send_json_success( array( 'message' => __( 'Sandbox cleared', 'plugin-wp-support-thisismyurl' ) ) );
	}
}
