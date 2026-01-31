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
 * @since      1.2604.0200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Phone Home Indicator Class
 *
 * Monitors and displays all outbound HTTP requests made by WPShadow.
 * Shows users exactly when and why we contact external services.
 *
 * @since 1.2604.0200
 */
class Phone_Home_Indicator {

	/**
	 * Recent connections storage.
	 *
	 * @var array
	 */
	private static $connections = array();

	/**
	 * Initialize phone home indicator.
	 *
	 * @since 1.2604.0200
	 * @return void
	 */
	public static function init() {
		// Hook into HTTP API to monitor outbound requests
		add_filter( 'pre_http_request', array( __CLASS__, 'track_outbound_request' ), 10, 3 );
		add_action( 'admin_notices', array( __CLASS__, 'show_indicator' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_wpshadow_get_recent_connections', array( __CLASS__, 'ajax_get_connections' ) );
	}

	/**
	 * Track outbound HTTP requests.
	 *
	 * @since  1.2604.0200
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
		set_transient( 'wpshadow_recent_connections', self::$connections, HOUR_IN_SECONDS );

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
	 * @since  1.2604.0200
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
	 * @since  1.2604.0200
	 * @return void
	 */
	public static function show_indicator() {
		// Only show on WPShadow pages
		$screen = get_current_screen();
		if ( ! $screen || false === strpos( $screen->id, 'wpshadow' ) ) {
			return;
		}

		// Get recent connections
		$connections = get_transient( 'wpshadow_recent_connections' ) ?: array();
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
			<p style="display: flex; align-items: center; gap: 10px; margin: 0.5em 0;">
				<span class="dashicons dashicons-admin-site-alt3" style="color: #6366F1; font-size: 20px; animation: wpshadowPulse 2s infinite;"></span>
				<strong><?php esc_html_e( 'Network Activity:', 'wpshadow' ); ?></strong>
				<?php
				printf(
					/* translators: %d: number of connections */
					esc_html( _n( 'WPShadow made %d external connection in the last 5 minutes.', 'WPShadow made %d external connections in the last 5 minutes.', $recent_count, 'wpshadow' ) ),
					esc_html( $recent_count )
				);
				?>
				<button type="button" class="button button-small" id="wpshadow-view-connections" style="margin-left: auto;">
					<?php esc_html_e( 'View Details', 'wpshadow' ); ?>
				</button>
			</p>
		</div>

		<div id="wpshadow-connections-modal" style="display: none;">
			<!-- Modal rendered via JavaScript -->
		</div>

		<style>
		@keyframes wpshadowPulse {
			0%, 100% { opacity: 1; }
			50% { opacity: 0.5; }
		}
		</style>
		<?php
	}

	/**
	 * Enqueue indicator assets.
	 *
	 * @since  1.2604.0200
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		// Only on WPShadow pages
		if ( false === strpos( $hook, 'wpshadow' ) ) {
			return;
		}

		wp_add_inline_script( 'jquery', self::get_indicator_js() );

		wp_localize_script( 'jquery', 'wpshadowPhoneHome', array(
			'nonce'    => wp_create_nonce( 'wpshadow_phone_home' ),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'strings'  => array(
				'loading'     => __( 'Loading connection details...', 'wpshadow' ),
				'no_data'     => __( 'No recent connections found.', 'wpshadow' ),
				'modal_title' => __( 'Recent Network Activity', 'wpshadow' ),
			),
		) );
	}

	/**
	 * Get indicator JavaScript.
	 *
	 * @since  1.2604.0200
	 * @return string JavaScript code.
	 */
	private static function get_indicator_js() {
		return <<<JS
jQuery(document).ready(function($) {
	$('#wpshadow-view-connections').on('click', function(e) {
		e.preventDefault();

		// Show loading modal
		var modal = $('<div>', {
			id: 'wpshadow-connections-modal-overlay',
			css: {
				position: 'fixed',
				top: 0,
				left: 0,
				right: 0,
				bottom: 0,
				background: 'rgba(0,0,0,0.7)',
				zIndex: 999999,
				display: 'flex',
				alignItems: 'center',
				justifyContent: 'center'
			}
		});

		var container = $('<div>', {
			css: {
				background: 'white',
				borderRadius: '8px',
				padding: '24px',
				maxWidth: '700px',
				maxHeight: '80vh',
				overflow: 'auto',
				boxShadow: '0 20px 60px rgba(0,0,0,0.3)'
			}
		});

		container.append('<h2>' + wpshadowPhoneHome.strings.modal_title + '</h2>');
		container.append('<p>' + wpshadowPhoneHome.strings.loading + '</p>');

		modal.append(container);
		$('body').append(modal);

		// Close on overlay click
		modal.on('click', function(e) {
			if (e.target === this) {
				$(this).fadeOut(200, function() { $(this).remove(); });
			}
		});

		// Fetch connections
		$.ajax({
			url: wpshadowPhoneHome.ajax_url,
			type: 'POST',
			data: {
				action: 'wpshadow_get_recent_connections',
				nonce: wpshadowPhoneHome.nonce
			},
			success: function(response) {
				if (response.success && response.data.connections) {
					var html = '<div style="margin-top: 16px;">';

					if (response.data.connections.length === 0) {
						html += '<p style="color: #6b7280; font-style: italic;">' + wpshadowPhoneHome.strings.no_data + '</p>';
					} else {
						html += '<table class="wp-list-table widefat fixed striped" style="margin-top: 16px;">';
						html += '<thead><tr>';
						html += '<th>' + 'Time' + '</th>';
						html += '<th>' + 'Domain' + '</th>';
						html += '<th>' + 'Purpose' + '</th>';
						html += '</tr></thead><tbody>';

						$.each(response.data.connections, function(i, conn) {
							var timeAgo = new Date(conn.timestamp).toLocaleTimeString();
							html += '<tr>';
							html += '<td>' + timeAgo + '</td>';
							html += '<td><code>' + conn.domain + '</code></td>';
							html += '<td>' + conn.purpose + '</td>';
							html += '</tr>';
						});

						html += '</tbody></table>';
					}

					html += '<div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">';
					html += '<p style="font-size: 13px; color: #6b7280;">';
					html += 'WPShadow is committed to transparency. All outbound connections are logged and visible to you. ';
					html += 'Visit <strong>WPShadow → Privacy</strong> to manage your data collection preferences.';
					html += '</p>';
					html += '<button type="button" class="button button-primary" id="wpshadow-close-modal">Close</button>';
					html += '</div>';
					html += '</div>';

					container.html('<h2>' + wpshadowPhoneHome.strings.modal_title + '</h2>' + html);

					$('#wpshadow-close-modal').on('click', function() {
						modal.fadeOut(200, function() { $(this).remove(); });
					});
				}
			}
		});
	});
});
JS;
	}

	/**
	 * AJAX handler to get recent connections.
	 *
	 * @since  1.2604.0200
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_get_connections() {
		// Use Security_Validator for consistent security checks
		if ( ! \WPShadow\Core\Security_Validator::verify_nonce( 'wpshadow_phone_home', 'nonce', false ) ||
			 ! \WPShadow\Core\Security_Validator::verify_capability( 'manage_options', false ) ) {
			wp_send_json_error( array( 'message' => \WPShadow\Core\Security_Validator::get_permission_error() ) );
		}

		$connections = get_transient( 'wpshadow_recent_connections' ) ?: array();

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
	 * @since  1.2604.0200
	 * @param  int $minutes Time window in minutes. Default 5.
	 * @return bool True if connections were made.
	 */
	public static function has_recent_activity( $minutes = 5 ) {
		$connections = get_transient( 'wpshadow_recent_connections' ) ?: array();
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
	 * @since  1.2604.0200
	 * @param  int $limit Number of connections to return. Default 10.
	 * @return array Recent connections.
	 */
	public static function get_recent_connections( $limit = 10 ) {
		$connections = get_transient( 'wpshadow_recent_connections' ) ?: array();

		// Sort by timestamp descending
		usort( $connections, function( $a, $b ) {
			return strtotime( $b['timestamp'] ) - strtotime( $a['timestamp'] );
		} );

		return array_slice( $connections, 0, $limit );
	}
}
