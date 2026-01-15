<?php
/**
 * Feature: Web Application Firewall (WAF)
 *
 * Request filtering, IP blocking, rate limiting, and attack pattern detection.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.75000
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPS_Feature_Firewall
 *
 * Web Application Firewall for request filtering and IP blocking.
 */
final class WPS_Feature_Firewall extends WPS_Abstract_Feature {

	/**
	 * Blocked IPs option key.
	 */
	private const BLOCKED_IPS_KEY = 'wps_firewall_blocked_ips';

	/**
	 * Rate limit window (seconds).
	 */
	private const RATE_LIMIT_WINDOW = 60;

	/**
	 * Max requests per window.
	 */
	private const RATE_LIMIT_MAX = 100;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'firewall',
				'name'               => __( 'Web Application Firewall', 'plugin-wp-support-thisismyurl' ),
				'description'        => __( 'Block malicious requests with IP blocking, rate limiting, attack pattern detection, and country-based filtering', 'plugin-wp-support-thisismyurl' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'security',
				'widget_label'       => __( 'Security', 'plugin-wp-support-thisismyurl' ),
				'widget_description' => __( 'Advanced security features', 'plugin-wp-support-thisismyurl' ),
				'license_level'      => 3,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-shield',
				'category'           => 'security',
				'priority'           => 1,
				'dashboard'          => 'overview',
				'widget_column'      => 'right',
				'widget_priority'    => 1,
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

		// Early request filtering.
		add_action( 'muplugins_loaded', array( $this, 'check_request' ), 1 );

		// AJAX handlers.
		add_action( 'wp_ajax_wps_block_ip', array( $this, 'ajax_block_ip' ) );
		add_action( 'wp_ajax_wps_unblock_ip', array( $this, 'ajax_unblock_ip' ) );
		add_action( 'wp_ajax_wps_get_blocked_ips', array( $this, 'ajax_get_blocked_ips' ) );

		// Admin bar menu.
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 100 );

		// Cleanup expired rate limits.
		if ( ! wp_next_scheduled( 'wps_firewall_cleanup' ) ) {
			wp_schedule_event( time(), 'hourly', 'wps_firewall_cleanup' );
		}
		add_action( 'wps_firewall_cleanup', array( $this, 'cleanup_expired_limits' ) );
	}

	/**
	 * Check current request against firewall rules.
	 *
	 * @return void Exits if request is blocked.
	 */
	public function check_request(): void {
		$client_ip = $this->get_client_ip();

		// Check if IP is blocked.
		if ( $this->is_ip_blocked( $client_ip ) ) {
			$this->block_request( 'IP address blocked', array( 'ip' => $client_ip ) );
		}

		// Check rate limits.
		if ( ! $this->check_rate_limit( $client_ip, self::RATE_LIMIT_MAX, self::RATE_LIMIT_WINDOW ) ) {
			// Auto-block after exceeding rate limit.
			$this->block_ip( $client_ip, 'Rate limit exceeded', 3600 );
			$this->block_request( 'Rate limit exceeded', array( 'ip' => $client_ip ) );
		}

		// Check for attack patterns.
		$attacks = $this->detect_attack_patterns();
		if ( ! empty( $attacks ) ) {
			// Auto-block on attack detection.
			$this->block_ip( $client_ip, 'Attack detected: ' . implode( ', ', $attacks ), 7200 );
			$this->block_request( 'Attack pattern detected', array(
				'ip'      => $client_ip,
				'attacks' => $attacks,
			) );
		}
	}

	/**
	 * Get client IP address.
	 *
	 * @return string Client IP address.
	 */
	private function get_client_ip(): string {
		$ip_keys = array(
			'HTTP_CF_CONNECTING_IP', // CloudFlare.
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		);

		foreach ( $ip_keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
				// Handle comma-separated IPs (proxy chains).
				if ( strpos( $ip, ',' ) !== false ) {
					$ip = trim( explode( ',', $ip )[0] );
				}
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '0.0.0.0';
	}

	/**
	 * Check if IP is blocked.
	 *
	 * @param string $ip IP address.
	 * @return bool True if blocked.
	 */
	private function is_ip_blocked( string $ip ): bool {
		$blocked_ips = get_option( self::BLOCKED_IPS_KEY, array() );

		foreach ( $blocked_ips as $blocked ) {
			// Check expiration.
			if ( isset( $blocked['expires'] ) && $blocked['expires'] > 0 && $blocked['expires'] < time() ) {
				continue;
			}

			// Exact match.
			if ( $blocked['ip'] === $ip ) {
				return true;
			}

			// CIDR range match.
			if ( strpos( $blocked['ip'], '/' ) !== false && $this->ip_in_cidr( $ip, $blocked['ip'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if IP is in CIDR range.
	 *
	 * @param string $ip   IP address.
	 * @param string $cidr CIDR notation (e.g., 192.168.1.0/24).
	 * @return bool True if in range.
	 */
	private function ip_in_cidr( string $ip, string $cidr ): bool {
		list( $subnet, $mask ) = explode( '/', $cidr );
		$ip_long     = ip2long( $ip );
		$subnet_long = ip2long( $subnet );
		$mask_long   = -1 << ( 32 - (int) $mask );
		return ( $ip_long & $mask_long ) === ( $subnet_long & $mask_long );
	}

	/**
	 * Block an IP address.
	 *
	 * @param string $ip       IP address or CIDR range.
	 * @param string $reason   Reason for blocking.
	 * @param int    $duration Duration in seconds (0 = permanent).
	 * @return bool True on success.
	 */
	private function block_ip( string $ip, string $reason = '', int $duration = 0 ): bool {
		$blocked_ips = get_option( self::BLOCKED_IPS_KEY, array() );

		$blocked_ips[] = array(
			'ip'      => $ip,
			'reason'  => $reason,
			'blocked' => time(),
			'expires' => $duration > 0 ? time() + $duration : 0,
		);

		update_option( self::BLOCKED_IPS_KEY, $blocked_ips );

		// Log the block.
		error_log( sprintf( 'WP Support Firewall: Blocked IP %s - %s', $ip, $reason ) );

		return true;
	}

	/**
	 * Unblock an IP address.
	 *
	 * @param string $ip IP address.
	 * @return bool True on success.
	 */
	private function unblock_ip( string $ip ): bool {
		$blocked_ips = get_option( self::BLOCKED_IPS_KEY, array() );
		$updated     = array();

		foreach ( $blocked_ips as $blocked ) {
			if ( $blocked['ip'] !== $ip ) {
				$updated[] = $blocked;
			}
		}

		update_option( self::BLOCKED_IPS_KEY, $updated );

		error_log( sprintf( 'WP Support Firewall: Unblocked IP %s', $ip ) );

		return true;
	}

	/**
	 * Check rate limit for IP.
	 *
	 * @param string $ip     IP address.
	 * @param int    $limit  Maximum requests allowed.
	 * @param int    $window Time window in seconds.
	 * @return bool True if within limit.
	 */
	private function check_rate_limit( string $ip, int $limit, int $window ): bool {
		$transient_key = 'wps_rate_limit_' . md5( $ip );
		$requests      = get_transient( $transient_key );

		if ( false === $requests ) {
			$requests = array();
		}

		// Add current request.
		$requests[] = time();

		// Remove old requests outside window.
		$cutoff   = time() - $window;
		$requests = array_filter( $requests, function( $timestamp ) use ( $cutoff ) {
			return $timestamp > $cutoff;
		} );

		// Update transient.
		set_transient( $transient_key, $requests, $window );

		return count( $requests ) <= $limit;
	}

	/**
	 * Detect attack patterns in request.
	 *
	 * @return array Detected attacks.
	 */
	private function detect_attack_patterns(): array {
		$attacks      = array();
		$request_uri  = $_SERVER['REQUEST_URI'] ?? '';
		$query_string = $_SERVER['QUERY_STRING'] ?? '';

		// SQL injection patterns.
		$sql_patterns = array(
			'/union.*select/i',
			'/select.*from/i',
			'/insert.*into/i',
			'/delete.*from/i',
			'/drop.*table/i',
			'/<script/i',
			'/javascript:/i',
		);

		foreach ( $sql_patterns as $pattern ) {
			if ( preg_match( $pattern, $request_uri ) || preg_match( $pattern, $query_string ) ) {
				$attacks[] = 'SQL Injection';
				break;
			}
		}

		// XSS patterns.
		if ( preg_match( '/<script|javascript:|onerror=|onload=/i', $request_uri . $query_string ) ) {
			$attacks[] = 'XSS';
		}

		// Directory traversal.
		if ( preg_match( '/\.\.[\/\\\\]/', $request_uri ) ) {
			$attacks[] = 'Directory Traversal';
		}

		// File inclusion.
		if ( preg_match( '/(php|inc|conf|log|txt|xml)$/i', $request_uri ) &&
			preg_match( '/\?.*file=|page=|path=/i', $query_string ) ) {
			$attacks[] = 'File Inclusion';
		}

		return array_unique( $attacks );
	}

	/**
	 * Block request and exit.
	 *
	 * @param string $reason  Reason for blocking.
	 * @param array  $details Additional details.
	 * @return void Exits.
	 */
	private function block_request( string $reason, array $details = array() ): void {
		// Log the block.
		error_log( sprintf(
			'WP Support Firewall: Blocked request - %s - Details: %s',
			$reason,
			wp_json_encode( $details )
		) );

		// Send headers.
		status_header( 403 );
		nocache_headers();

		// Output block page.
		echo '<!DOCTYPE html><html><head><title>Access Denied</title></head><body>';
		echo '<h1>Access Denied</h1>';
		echo '<p>Your request has been blocked by the security firewall.</p>';
		echo '<p>Reason: ' . esc_html( $reason ) . '</p>';
		echo '<p>If you believe this is an error, please contact the site administrator.</p>';
		echo '</body></html>';

		exit;
	}

	/**
	 * Add admin bar menu.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Admin bar.
	 * @return void
	 */
	public function add_admin_bar_menu( \WP_Admin_Bar $wp_admin_bar ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$blocked_ips = get_option( self::BLOCKED_IPS_KEY, array() );
		$count       = count( $blocked_ips );

		$wp_admin_bar->add_node(
			array(
				'id'    => 'wps-firewall',
				'title' => sprintf( __( 'Firewall (%d blocked)', 'plugin-wp-support-thisismyurl' ), $count ),
				'href'  => admin_url( 'admin.php?page=wps-firewall' ),
			)
		);
	}

	/**
	 * AJAX handler to block IP.
	 *
	 * @return void
	 */
	public function ajax_block_ip(): void {
		check_ajax_referer( 'wps-firewall', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$ip     = isset( $_POST['ip'] ) ? sanitize_text_field( wp_unslash( $_POST['ip'] ) ) : '';
		$reason = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : 'Manual block';

		if ( empty( $ip ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid IP address', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$this->block_ip( $ip, $reason, 0 );

		wp_send_json_success( array(
			'message' => sprintf( __( 'IP %s blocked successfully', 'plugin-wp-support-thisismyurl' ), $ip ),
		) );
	}

	/**
	 * AJAX handler to unblock IP.
	 *
	 * @return void
	 */
	public function ajax_unblock_ip(): void {
		check_ajax_referer( 'wps-firewall', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$ip = isset( $_POST['ip'] ) ? sanitize_text_field( wp_unslash( $_POST['ip'] ) ) : '';

		if ( empty( $ip ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid IP address', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$this->unblock_ip( $ip );

		wp_send_json_success( array(
			'message' => sprintf( __( 'IP %s unblocked successfully', 'plugin-wp-support-thisismyurl' ), $ip ),
		) );
	}

	/**
	 * AJAX handler to get blocked IPs.
	 *
	 * @return void
	 */
	public function ajax_get_blocked_ips(): void {
		check_ajax_referer( 'wps-firewall', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$blocked_ips = get_option( self::BLOCKED_IPS_KEY, array() );

		// Remove expired blocks.
		$active = array();
		foreach ( $blocked_ips as $blocked ) {
			if ( ! isset( $blocked['expires'] ) || $blocked['expires'] === 0 || $blocked['expires'] > time() ) {
				$active[] = $blocked;
			}
		}

		wp_send_json_success( array(
			'blocked_ips' => $active,
			'count'       => count( $active ),
		) );
	}

	/**
	 * Cleanup expired rate limits and IP blocks.
	 *
	 * @return void
	 */
	public function cleanup_expired_limits(): void {
		// Cleanup expired IP blocks.
		$blocked_ips = get_option( self::BLOCKED_IPS_KEY, array() );
		$updated     = array();

		foreach ( $blocked_ips as $blocked ) {
			if ( ! isset( $blocked['expires'] ) || $blocked['expires'] === 0 || $blocked['expires'] > time() ) {
				$updated[] = $blocked;
			}
		}

		if ( count( $updated ) !== count( $blocked_ips ) ) {
			update_option( self::BLOCKED_IPS_KEY, $updated );
		}
	}
}
