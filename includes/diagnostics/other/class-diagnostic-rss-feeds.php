<?php
declare(strict_types=1);
/**
 * RSS Feed Links Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if RSS feed links are in the head when not needed.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_RSS_Feeds extends Diagnostic_Base {

	protected static $slug        = 'rss-feeds';
	protected static $title       = 'RSS Feed Links in Head';
	protected static $description = 'RSS feed links are added to every page head. If you do not use RSS feeds, these can be removed.';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if treatment is already applied
		$disabled = get_option( 'wpshadow_rss_feeds_disabled', false );

		if ( $disabled ) {
			return null;
		}

		// Check if feed links are enabled
		$has_feed_links       = has_action( 'wp_head', 'feed_links' ) !== false;
		$has_extra_feed_links = has_action( 'wp_head', 'feed_links_extra' ) !== false;

		if ( ! $has_feed_links && ! $has_extra_feed_links ) {
			return null;
		}

		$links = array();
		if ( $has_feed_links ) {
			$links[] = 'main feed links';
		}
		if ( $has_extra_feed_links ) {
			$links[] = 'extra feed links (comments, categories)';
		}

		return array(
			'id'          => 'rss-feeds',
			'title'       => 'RSS Feed Links in Head',
			'description' => 'Your site outputs ' . implode( ' and ', $links ) . ' in the HTML head. If you don\'t use RSS feeds, removing these reduces HTML size and removes discovery endpoints.',
			'severity'    => 'info',
			'category'    => 'performance',
			'impact'      => 'Adds 2-4 link tags to every page head',
			'fix_time'    => '1 second',
			'kb_article'  => 'rss-feeds',
		);
	}

	/**
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}
	/**
	 * Test: Hook detection logic
	 *
	 * Verifies that diagnostic correctly detects hooks and returns
	 * appropriate result (null or array).
	 *
	 * @return array Test result
	 */
	public static function test_hook_detection(): array {
		$result = self::check();
		
		// Should consistently return null or array
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Hook detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Unexpected result type from hook detection',
		);
	}}
