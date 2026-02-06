<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

use WPShadow\Core\Hook_Subscriber_Base;

/**
 * Bot Traffic Analyzer
 *
 * Monitors bot traffic patterns to identify malicious bots consuming resources
 * and impacting site performance.
 *
 * Philosophy: Show value (#9) - Protect server resources from bot abuse.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.6030.2200
 */
class Bot_Traffic_Analyzer extends Hook_Subscriber_Base {

	/**
	 * Known good bot patterns
	 *
	 * @var array
	 */
	private static $good_bots = array(
		'Googlebot',
		'Bingbot',
		'Slurp', // Yahoo
		'DuckDuckBot',
		'Baiduspider',
		'YandexBot',
		'facebookexternalhit',
		'Twitterbot',
		'LinkedInBot',
		'Pinterestbot',
		'Discordbot',
		'Slackbot',
		'WhatsApp',
	);

	/**
	 * Known bad bot patterns
	 *
	 * @var array
	 */
	private static $bad_bots = array(
		'scrapy',
		'curl',
		'wget',
		'python-requests',
		'Go-http-client',
		'Nutch',
		'MJ12bot',
		'AhrefsBot',
		'SemrushBot',
		'DotBot',
		'BLEXBot',
		'MegaIndex',
	);

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'init'                          => array( 'track_request', 1 ),
			'wpshadow_analyze_bot_traffic'  => 'analyze',
		);
	}

	/**
	 * Initialize bot traffic monitoring (deprecated)
	 *
	 * @deprecated 1.7035.1400 Use Bot_Traffic_Analyzer::subscribe() instead
	 * @return     void
	 */
	public static function init(): void {
		// Schedule cron
		if ( ! wp_next_scheduled( 'wpshadow_analyze_bot_traffic' ) ) {
			wp_schedule_event( time(), 'hourly', 'wpshadow_analyze_bot_traffic' );
		}

		// Subscribe to hooks
		self::subscribe();
		add_action( 'wpshadow_analyze_bot_traffic', array( __CLASS__, 'analyze' ) );
	}

	/**
	 * Track request
	 *
	 * @return void
	 */
	public static function track_request(): void {
		$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
		if ( empty( $user_agent ) ) {
			return;
		}

		// Detect if bot
		$bot_type = self::detect_bot( $user_agent );
		if ( ! $bot_type ) {
			return; // Not a bot
		}

		$requests = \WPShadow\Core\Cache_Manager::get( 'bot_traffic_data', 'wpshadow_monitoring' );
		if ( ! is_array( $requests ) ) {
			$requests = array();
		}

		$requests[] = array(
			'bot_type'   => $bot_type,
			'user_agent' => substr( $user_agent, 0, 200 ), // Limit length
			'ip'         => self::get_client_ip(),
			'url'        => $_SERVER['REQUEST_URI'] ?? '',
			'timestamp'  => time(),
		);

		// Keep only last 24 hours
		$one_day_ago = time() - DAY_IN_SECONDS;
		$requests    = array_filter(
			$requests,
			function ( $item ) use ( $one_day_ago ) {
				return $item['timestamp'] > $one_day_ago;
			}
		);

		// Limit to 2000 entries
		if ( count( $requests ) > 2000 ) {
			$requests = array_slice( $requests, -2000 );
		}

		\WPShadow\Core\Cache_Manager::set( 'bot_traffic_data', $requests, DAY_IN_SECONDS , 'wpshadow_monitoring');
	}

	/**
	 * Detect bot type
	 *
	 * @param string $user_agent User agent string
	 * @return string|false Bot type or false if not a bot
	 */
	private static function detect_bot( string $user_agent ) {
		// Check good bots
		foreach ( self::$good_bots as $bot ) {
			if ( stripos( $user_agent, $bot ) !== false ) {
				return 'good';
			}
		}

		// Check bad bots
		foreach ( self::$bad_bots as $bot ) {
			if ( stripos( $user_agent, $bot ) !== false ) {
				return 'bad';
			}
		}

		// Check for generic bot patterns
		if ( preg_match( '/(bot|crawler|spider|scraper|scan|check)/i', $user_agent ) ) {
			return 'unknown';
		}

		return false;
	}

	/**
	 * Analyze bot traffic
	 *
	 * @return array Analysis results
	 */
	public static function analyze(): array {
		$data = \WPShadow\Core\Cache_Manager::get( 'bot_traffic_data', 'wpshadow_monitoring' );

		$results = array(
			'total_bot_requests' => 0,
			'good_bot_count'     => 0,
			'bad_bot_count'      => 0,
			'unknown_bot_count'  => 0,
			'requests_per_hour'  => 0,
			'top_bots'           => array(),
			'suspicious_ips'     => array(),
			'is_excessive'       => false,
		);

		if ( ! is_array( $data ) || empty( $data ) ) {
			\WPShadow\Core\Cache_Manager::set( 'bot_traffic', $results, HOUR_IN_SECONDS , 'wpshadow_monitoring');
			return $results;
		}

		$results['total_bot_requests'] = count( $data );

		// Count by bot type
		$bot_counts    = array(
			'good'    => 0,
			'bad'     => 0,
			'unknown' => 0,
		);
		$by_user_agent = array();
		$by_ip         = array();

		foreach ( $data as $request ) {
			$type = $request['bot_type'];
			++$bot_counts[ $type ];

			// Count by user agent
			$ua = $request['user_agent'];
			if ( ! isset( $by_user_agent[ $ua ] ) ) {
				$by_user_agent[ $ua ] = array(
					'count' => 0,
					'type'  => $type,
				);
			}
			++$by_user_agent[ $ua ]['count'];

			// Count by IP
			$ip = $request['ip'];
			if ( ! isset( $by_ip[ $ip ] ) ) {
				$by_ip[ $ip ] = 0;
			}
			++$by_ip[ $ip ];
		}

		$results['good_bot_count']    = $bot_counts['good'];
		$results['bad_bot_count']     = $bot_counts['bad'];
		$results['unknown_bot_count'] = $bot_counts['unknown'];

		// Calculate requests per hour (based on 24h of data)
		$results['requests_per_hour'] = (int) ( $results['total_bot_requests'] / 24 );

		// Top bots
		uasort(
			$by_user_agent,
			function ( $a, $b ) {
				return $b['count'] - $a['count'];
			}
		);
		$results['top_bots'] = array_slice( $by_user_agent, 0, 10, true );

		// Suspicious IPs (>100 requests in 24h)
		foreach ( $by_ip as $ip => $count ) {
			if ( $count > 100 ) {
				$results['suspicious_ips'][ $ip ] = $count;
			}
		}

		// Excessive if >500 bot requests per hour or >50 bad bot requests
		$results['is_excessive'] = $results['requests_per_hour'] > 500 ||
									$results['bad_bot_count'] > 50;

		// Set cache for diagnostic
		\WPShadow\Core\Cache_Manager::set( 'bot_traffic', $results, HOUR_IN_SECONDS , 'wpshadow_monitoring');

		return $results;
	}

	/**
	 * Get client IP address
	 *
	 * @return string IP address
	 */
	private static function get_client_ip(): string {
		$ip_keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR' );
		foreach ( $ip_keys as $key ) {
			if ( isset( $_SERVER[ $key ] ) ) {
				$ip = $_SERVER[ $key ];
				if ( strpos( $ip, ',' ) !== false ) {
					$ip = trim( explode( ',', $ip )[0] );
				}
				return $ip;
			}
		}
		return 'unknown';
	}

	/**
	 * Get summary
	 *
	 * @return array Summary data
	 */
	public static function get_summary(): array {
		$results = \WPShadow\Core\Cache_Manager::get( 'bot_traffic', 'wpshadow_monitoring' );
		return is_array( $results ) ? $results : array(
			'total_bot_requests' => 0,
			'requests_per_hour'  => 0,
			'is_excessive'       => false,
		);
	}

	/**
	 * Clear cached data
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		\WPShadow\Core\Cache_Manager::delete( 'bot_traffic_data', 'wpshadow_monitoring' );
		\WPShadow\Core\Cache_Manager::delete( 'bot_traffic', 'wpshadow_monitoring' );
	}
}
