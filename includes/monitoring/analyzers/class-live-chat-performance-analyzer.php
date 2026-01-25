<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Live Chat Performance Analyzer
 *
 * Monitors live chat widget performance impact on page load times.
 * Identifies slow-loading chat services that hurt user experience.
 *
 * Philosophy: Show value (#9) - Optimize chat widget performance.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.2601.2200
 */
class Live_Chat_Performance_Analyzer {

	/**
	 * Known chat widget domains
	 *
	 * @var array
	 */
	private static $chat_domains = array(
		'intercom.io',
		'intercomcdn.com',
		'drift.com',
		'driftt.com',
		'tawk.to',
		'tidio.co',
		'crisp.chat',
		'livechatinc.com',
		'zendesk.com',
		'zopim.com',
		'olark.com',
		'liveperson.net',
		'purechat.com',
		'freshchat.com',
		'helpscout.net',
	);

	/**
	 * Analyze chat widget performance
	 *
	 * @return array Analysis results
	 */
	public static function analyze(): array {
		// Check cache first (hourly)
		$cached = get_transient( 'wpshadow_live_chat_performance' );
		if ( $cached && is_array( $cached ) ) {
			return $cached;
		}

		$results = array(
			'has_chat'               => false,
			'chat_services'          => array(),
			'total_scripts'          => 0,
			'estimated_load_time_ms' => 0,
			'is_slow'                => false,
		);

		// Get enqueued scripts
		global $wp_scripts;

		if ( ! isset( $wp_scripts ) || ! ( $wp_scripts instanceof \WP_Scripts ) ) {
			set_transient( 'wpshadow_live_chat_performance', $results, HOUR_IN_SECONDS );
			return $results;
		}

		// Find chat scripts
		$chat_scripts = array();
		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( ! is_string( $script->src ) || empty( $script->src ) ) {
				continue;
			}

			// Check if script is from a chat service
			$is_chat = false;
			$service = '';

			foreach ( self::$chat_domains as $domain ) {
				if ( strpos( $script->src, $domain ) !== false ) {
					$is_chat = true;
					$service = $domain;
					break;
				}
			}

			if ( $is_chat ) {
				$chat_scripts[] = array(
					'handle'  => $handle,
					'service' => $service,
					'src'     => $script->src,
				);
			}
		}

		if ( ! empty( $chat_scripts ) ) {
			$results['has_chat']      = true;
			$results['total_scripts'] = count( $chat_scripts );

			// Group by service
			$by_service = array();
			foreach ( $chat_scripts as $script ) {
				$service = $script['service'];
				if ( ! isset( $by_service[ $service ] ) ) {
					$by_service[ $service ] = array(
						'scripts' => array(),
						'count'   => 0,
					);
				}
				$by_service[ $service ]['scripts'][] = $script['handle'];
				++$by_service[ $service ]['count'];
			}

			$results['chat_services'] = $by_service;

			// Estimate load time (rough: 200ms per script + 500ms base for chat init)
			$results['estimated_load_time_ms'] = 500 + ( count( $chat_scripts ) * 200 );

			// Consider slow if >1 second
			$results['is_slow'] = $results['estimated_load_time_ms'] > 1000;
		}

		// Check for inline chat scripts in content
		$results['has_inline_chat'] = self::check_inline_chat_scripts();

		// Cache for 1 hour
		set_transient( 'wpshadow_live_chat_performance', $results, HOUR_IN_SECONDS );

		return $results;
	}

	/**
	 * Check for inline chat scripts in content
	 *
	 * @return bool True if inline chat found
	 */
	private static function check_inline_chat_scripts(): bool {
		// Get recent posts to check for inline chat code
		$posts = get_posts(
			array(
				'posts_per_page' => 10,
				'post_type'      => 'any',
				'post_status'    => 'publish',
			)
		);

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Check for common chat snippets
			foreach ( self::$chat_domains as $domain ) {
				if ( strpos( $content, $domain ) !== false ) {
					return true;
				}
			}

			// Check for common chat widget patterns
			$patterns = array(
				'window.Intercom',
				'window.Drift',
				'Tawk_API',
				'tidioIdentify',
				'$crisp',
				'LiveChatWidget',
				'zE(',
				'olark(',
			);

			foreach ( $patterns as $pattern ) {
				if ( strpos( $content, $pattern ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get summary
	 *
	 * @return array Summary data
	 */
	public static function get_summary(): array {
		$results = get_transient( 'wpshadow_live_chat_performance' );
		return is_array( $results ) ? $results : array(
			'has_chat'      => false,
			'chat_services' => array(),
			'is_slow'       => false,
		);
	}

	/**
	 * Clear cached data
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		delete_transient( 'wpshadow_live_chat_performance' );
	}
}
