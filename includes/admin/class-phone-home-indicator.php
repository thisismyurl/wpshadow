<?php
/**
 * Phone Home Indicator
 *
 * Shows users when WPShadow is contacting external servers.
 * Complete transparency about outbound connections.
 * Phase 6: Privacy & Consent Excellence
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since      1.6004.0200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Phone Home Indicator Class
 *
 * Monitors and displays all outbound HTTP requests made by WPShadow.
 * Shows users exactly when and why we contact external services.
 *
 * @since 1.6004.0200
 */
class Phone_Home_Indicator extends Hook_Subscriber_Base {

	/**
	 * Recent connections storage.
	 *
	 * @var array
	 */
	private static $connections = array();

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'pre_http_request'                                => array( 'track_outbound_request', 10, 3 ),
			'admin_notices'                                   => 'show_indicator',
			'admin_enqueue_scripts'                           => 'enqueue_assets',
			'wp_ajax_wpshadow_get_recent_connections'         => 'ajax_get_connections',
		);
	}

	/**
	 * Initialize phone home indicator (deprecated)
	 *
	 * @deprecated 1.7035.1400 Use Phone_Home_Indicator::subscribe() instead
	 * @since      1.6004.0200
	 * @return     void
	 */
	public static function init() {
		self::subscribe();
	}

	/**
	 * Track outbound HTTP requests.
	 *
	 * @since  1.6004.0200
	 * @param  false|array|WP_Error $preempt Response to short-circuit with.
	 * @param  array                $args Request arguments.
	 * @param  string               $url Request URL.
	 * @return false|array|WP_Error Unchanged preempt value.
	 */
	public static function track_outbound_request( $preempt, $args, $url ) {
		// Only track WPShadow-initiated requests
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 10 );
		$is_wpshadow = false;

		foreach ( $backtrace as $trace ) {
			if ( isset( $trace['file'] ) && false !== strpos( $trace['file'], 'wpshadow' ) ) {
				$is_wpshadow = true;
				break;
			}
		}

		if ( ! $is_wpshadow ) {
			return $preempt;
		}

		// Parse URL to get domain
		$parsed_url = wp_parse_url( $url );
		$domain     = isset( $parsed_url['host'] ) ? $parsed_url['host'] : 'unknown';

		// Determine purpose
		$purpose = self::determine_purpose( $url, $args );

		// Store connection info
		self::$connections[] = array(
			'timestamp' => current_time( 'mysql' ),
			'domain'    => $domain,
			'url'       => $url,
			'purpose'   => $purpose,
			'method'    => isset( $args['method'] ) ? $args['method'] : 'GET',
		);

		// Keep only last 10 connections
		self::$connections = array_slice( self::$connections, -10 );

		// Store in transient for persistence across page loads
		\WPShadow\Core\Cache_Manager::set(
			'recent_connections',
			self::$connections,
			HOUR_IN_SECONDS,
			'wpshadow_phone_home'
		);

		// Log activity
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'outbound_connection',
				sprintf( 'Contacted %s: %s', $domain, $purpose ),
				'',
				array(
					'domain'  => $domain,
					'purpose' => $purpose,
				)
			);
		}

		return $preempt;
	}

	/**
	 * Determine the purpose of an outbound request.
	 *
	 * @since  1.6004.0200
	 * @param  string $url Request URL.
	 * @param  array  $args Request arguments.
	 * @return string Purpose description.
	 */
	private static function determine_purpose( $url, $args ) {
		$parsed = wp_parse_url( $url );
		$domain = isset( $parsed['host'] ) ? $parsed['host'] : '';
		$path   = isset( $parsed['path'] ) ? $parsed['path'] : '';

		// WPShadow.com
		if ( false !== strpos( $domain, 'wpshadow.com' ) ) {
			if ( false !== strpos( $path, '/kb/' ) ) {
				return __( 'Fetching knowledge base article', 'wpshadow' );
			} elseif ( false !== strpos( $path, '/academy/' ) ) {
				return __( 'Fetching training video', 'wpshadow' );
			} elseif ( false !== strpos( $path, '/api/' ) ) {
				return __( 'Checking for updates', 'wpshadow' );
			}
			return __( 'Contacting WPShadow.com', 'wpshadow' );
		}

		// WordPress.org
		if ( false !== strpos( $domain, 'wordpress.org' ) ) {
			return __( 'Checking for plugin updates', 'wpshadow' );
		}

		// Analytics (if telemetry enabled)
		if ( false !== strpos( $domain, 'analytics' ) ) {
			return __( 'Sending anonymous usage data', 'wpshadow' );
		}

		// Default
		return __( 'External service request', 'wpshadow' );
	}

	/**
	 * Show phone home indicator notice.
	 *
	 * @since  1.6004.0200
	 * @return void
	 */
	public static function show_indicator() {
		// Only show on WPShadow pages
		$screen = get_current_screen();
		if ( ! $screen || false === strpos( $screen->id, 'wpshadow' ) ) {
			return;
		}

		// Get recent connections
		$connections = \WPShadow\Core\Cache_Manager::get( 'recent_connections', 'wpshadow_admin' ) ?: array();
		if ( empty( $connections ) ) {
			return;
		}

		// Count connections in last 5 minutes
		$recent_count = 0;
		$five_min_ago = time() - ( 5 * MINUTE_IN_SECONDS );

		foreach ( $connections as $conn ) {
			if ( strtotime( $conn['timestamp'] ) > $five_min_ago ) {
				$recent_count++;
			}
		}

		if ( 0 === $recent_count ) {
			return;
		}

		?>
		<div class="notice notice-info is-dismissible wpshadow-phone-home-indicator">
			<p class="wps-phone-home-indicator">
				<span class="dashicons dashicons-admin-site-alt3 wps-phone-home-icon"></span>
				<strong><?php esc_html_e( 'Network Activity:', 'wpshadow' ); ?></strong>
				<?php
				printf(
					/* translators: %d: number of connections */
					esc_html( _n( 'WPShadow made %d external connection in the last 5 minutes.', 'WPShadow made %d external connections in the last 5 minutes.', $recent_count, 'wpshadow' ) ),
					esc_html( $recent_count )
				);
				?>
				<button type="button" class="button button-small wps-phone-home-view-btn" id="wpshadow-view-connections">
					<?php esc_html_e( 'View Details', 'wpshadow' ); ?>
				</button>
			</p>
		</div>

		<div id="wpshadow-connections-modal" class="wps-phone-home-modal">
			<!-- Modal rendered via JavaScript -->
		</div>
		<?php
	}

	/**
	 * Enqueue indicator assets.
	 *
	 * @since  1.6004.0200
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		// Only on WPShadow pages
		if ( false === strpos( $hook, 'wpshadow' ) ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-phone-home',
			WPSHADOW_URL . 'assets/css/phone-home-indicator.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-phone-home',
			WPSHADOW_URL . 'assets/js/phone-home-indicator.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-phone-home',
			'wpshadowPhoneHome',
			array(
				'nonce'    => wp_create_nonce( 'wpshadow_phone_home' ),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'strings'  => array(
					'loading'     => __( 'Loading connection details...', 'wpshadow' ),
					'no_data'     => __( 'No recent connections found.', 'wpshadow' ),
					'modal_title' => __( 'Recent Network Activity', 'wpshadow' ),
				),
			)
		);
	}

	/**
	 * AJAX handler to get recent connections.
	 *
	 * @since  1.6004.0200
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_get_connections() {
		// Use Security_Validator for consistent security checks
		if ( ! \WPShadow\Core\Security_Validator::verify_nonce( 'wpshadow_phone_home', 'nonce', false ) ||
			 ! \WPShadow\Core\Security_Validator::verify_capability( 'manage_options', false ) ) {
			wp_send_json_error( array( 'message' => \WPShadow\Core\Security_Validator::get_permission_error() ) );
		}

		$connections = \WPShadow\Core\Cache_Manager::get(
			'recent_connections',
			'wpshadow_phone_home',
			array()
		);

		// Sort by timestamp descending (most recent first)
		usort( $connections, function( $a, $b ) {
			return strtotime( $b['timestamp'] ) - strtotime( $a['timestamp'] );
		} );

		wp_send_json_success( array(
			'connections' => $connections,
		) );
	}

	/**
	 * Check if any outbound connections were made recently.
	 *
	 * Public method for other components to check phone-home status.
	 *
	 * @since  1.6004.0200
	 * @param  int $minutes Time window in minutes. Default 5.
	 * @return bool True if connections were made.
	 */
	public static function has_recent_activity( $minutes = 5 ) {
		$connections = \WPShadow\Core\Cache_Manager::get(
			'recent_connections',
			'wpshadow_phone_home',
			array()
		);
		$cutoff_time = time() - ( $minutes * MINUTE_IN_SECONDS );

		foreach ( $connections as $conn ) {
			if ( strtotime( $conn['timestamp'] ) > $cutoff_time ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get recent connections for display.
	 *
	 * @since  1.6004.0200
	 * @param  int $limit Number of connections to return. Default 10.
	 * @return array Recent connections.
	 */
	public static function get_recent_connections( $limit = 10 ) {
		$connections = \WPShadow\Core\Cache_Manager::get(
			'recent_connections',
			'wpshadow_phone_home',
			array()
		);

		// Sort by timestamp descending
		usort( $connections, function( $a, $b ) {
			return strtotime( $b['timestamp'] ) - strtotime( $a['timestamp'] );
		} );

		return array_slice( $connections, 0, $limit );
	}
}
