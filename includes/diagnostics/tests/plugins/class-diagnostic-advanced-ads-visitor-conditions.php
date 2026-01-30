<?php
/**
 * Advanced Ads Visitor Conditions Diagnostic
 *
 * Visitor conditions causing database strain.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.292.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Ads Visitor Conditions Diagnostic Class
 *
 * @since 1.292.0000
 */
class Diagnostic_AdvancedAdsVisitorConditions extends Diagnostic_Base {

	protected static $slug = 'advanced-ads-visitor-conditions';
	protected static $title = 'Advanced Ads Visitor Conditions';
	protected static $description = 'Visitor conditions causing database strain';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'ADVADS_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Complex visitor conditions.
		global $wpdb;
		$complex_conditions = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND LENGTH(meta_value) > 1000",
				'_advads_visitor_conditions'
			)
		);
		if ( $complex_conditions > 5 ) {
			$issues[] = "{$complex_conditions} ads with very complex visitor conditions (performance impact)";
		}
		
		// Check 2: Database queries per page.
		$condition_types = get_option( 'advads_condition_types', array() );
		$db_intensive = array( 'post_type', 'taxonomy', 'user_role' );
		$db_conditions = array_intersect( $condition_types, $db_intensive );
		if ( ! empty( $db_conditions ) ) {
			$count = count( $db_conditions );
			$issues[] = "{$count} database-intensive condition types active (slow queries)";
		}
		
		// Check 3: Condition caching disabled.
		$condition_cache = get_option( 'advads_cache_visitor_conditions', '0' );
		if ( '0' === $condition_cache ) {
			$issues[] = 'visitor condition caching disabled (repeated evaluations)';
		}
		
		// Check 4: User meta queries.
		$usermeta_conditions = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE %s",
				'_advads_visitor_conditions',
				'%user_meta%'
			)
		);
		if ( $usermeta_conditions > 0 ) {
			$issues[] = "{$usermeta_conditions} ads querying user meta (expensive on each page load)";
		}
		
		// Check 5: JavaScript condition evaluation.
		$js_conditions = get_option( 'advads_js_conditions', '0' );
		if ( '1' === $js_conditions && '0' === $condition_cache ) {
			$issues[] = 'JavaScript conditions without server-side caching (double evaluation)';
		}
		
		// Check 6: Condition evaluation timing.
		$eval_timing = get_option( 'advads_condition_eval_timing', 'wp_head' );
		if ( 'wp_head' === $eval_timing ) {
			$issues[] = 'conditions evaluated in wp_head (blocks page rendering)';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Advanced Ads visitor conditions performance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/advanced-ads-visitor-conditions',
			);
		}
		
		return null;
	}
}
