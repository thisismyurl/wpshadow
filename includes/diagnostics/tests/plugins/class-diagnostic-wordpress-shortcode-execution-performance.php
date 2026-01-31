<?php
/**
 * Wordpress Shortcode Execution Performance Diagnostic
 *
 * Wordpress Shortcode Execution Performance issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1286.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Shortcode Execution Performance Diagnostic Class
 *
 * @since 1.1286.0000
 */
class Diagnostic_WordpressShortcodeExecutionPerformance extends Diagnostic_Base {

	protected static $slug = 'wordpress-shortcode-execution-performance';
	protected static $title = 'Wordpress Shortcode Execution Performance';
	protected static $description = 'Wordpress Shortcode Execution Performance issue detected';
	protected static $family = 'performance';

	public static function check() {
		global $shortcode_tags;

		$issues = array();

		// Check 1: Verify shortcode count is reasonable
		$shortcode_count = is_array( $shortcode_tags ) ? count( $shortcode_tags ) : 0;
		if ( $shortcode_count > 80 ) {
			$issues[] = 'High number of registered shortcodes (over 80)';
		}

		// Check 2: Check for shortcode execution in widgets
		$widget_shortcodes = get_option( 'widget_text', array() );
		if ( ! empty( $widget_shortcodes ) ) {
			$issues[] = 'Shortcodes enabled in text widgets (performance impact)';
		}

		// Check 3: Verify shortcode caching
		$shortcode_cache = get_option( 'shortcode_cache_enabled', 0 );
		if ( ! $shortcode_cache ) {
			$issues[] = 'Shortcode output caching not enabled';
		}

		// Check 4: Check for shortcode recursion protection
		$recursion_limit = get_option( 'shortcode_recursion_limit', 0 );
		if ( $recursion_limit <= 0 ) {
			$issues[] = 'Shortcode recursion limit not configured';
		}

		// Check 5: Verify selective shortcode loading
		$selective_loading = get_option( 'shortcode_selective_loading', 0 );
		if ( ! $selective_loading ) {
			$issues[] = 'Selective shortcode loading not enabled';
		}

		// Check 6: Check for nested shortcodes
		$nested_shortcodes = get_option( 'shortcode_nested_enabled', 0 );
		if ( $nested_shortcodes ) {
			$issues[] = 'Nested shortcodes enabled (can slow rendering)';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d shortcode performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wordpress-shortcode-execution-performance',
			);
		}

		return null;
	}
}
