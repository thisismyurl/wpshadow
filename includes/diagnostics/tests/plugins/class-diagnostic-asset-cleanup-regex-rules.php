<?php
/**
 * Asset Cleanup Regex Rules Diagnostic
 *
 * Asset Cleanup Regex Rules not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.928.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asset Cleanup Regex Rules Diagnostic Class
 *
 * @since 1.928.0000
 */
class Diagnostic_AssetCleanupRegexRules extends Diagnostic_Base {

	protected static $slug = 'asset-cleanup-regex-rules';
	protected static $title = 'Asset Cleanup Regex Rules';
	protected static $description = 'Asset Cleanup Regex Rules not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'AssetCleanUp' ) && ! function_exists( 'assetcleanup_is_active' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Regex rules configured.
		$regex_rules = get_option( 'asset_cleanup_regex_rules', array() );
		if ( empty( $regex_rules ) || ! is_array( $regex_rules ) ) {
			$issues[] = 'no regex rules configured';
		}
		
		// Check 2: Invalid regex patterns.
		$invalid_count = 0;
		if ( is_array( $regex_rules ) ) {
			foreach ( $regex_rules as $rule ) {
				if ( ! empty( $rule['pattern'] ) && false === @preg_match( '/' . $rule['pattern'] . '/', '' ) ) {
					$invalid_count++;
				}
			}
		}
		if ( $invalid_count > 0 ) {
			$issues[] = "{$invalid_count} invalid regex patterns";
		}
		
		// Check 3: Performance impact.
		$rule_count = is_array( $regex_rules ) ? count( $regex_rules ) : 0;
		if ( $rule_count > 50 ) {
			$issues[] = "{$rule_count} rules (high performance impact)";
		}
		
		// Check 4: Rule duplicates.
		$patterns = array();
		$dupes = 0;
		foreach ( (array) $regex_rules as $rule ) {
			if ( ! empty( $rule['pattern'] ) ) {
				if ( in_array( $rule['pattern'], $patterns, true ) ) {
					$dupes++;
				}
				$patterns[] = $rule['pattern'];
			}
		}
		if ( $dupes > 0 ) {
			$issues[] = "{$dupes} duplicate patterns";
		}
		
		// Check 5: Case sensitivity.
		$case_insensitive = get_option( 'asset_cleanup_regex_case_insensitive', '0' );
		if ( '1' === $case_insensitive && $rule_count > 10 ) {
			$issues[] = 'case-insensitive matching with many rules';
		}
		
		// Check 6: Debugging enabled.
		$debug = get_option( 'asset_cleanup_regex_debug', '0' );
		if ( '1' === $debug ) {
			$issues[] = 'regex debugging enabled in production';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 45 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Asset Cleanup regex issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/asset-cleanup-regex-rules',
			);
		}
		
		return null;
	}
}
