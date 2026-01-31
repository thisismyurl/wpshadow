<?php
/**
 * Oxygen Builder Reusable Parts Diagnostic
 *
 * Oxygen Builder Reusable Parts issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.815.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Oxygen Builder Reusable Parts Diagnostic Class
 *
 * @since 1.815.0000
 */
class Diagnostic_OxygenBuilderReusableParts extends Diagnostic_Base {

	protected static $slug = 'oxygen-builder-reusable-parts';
	protected static $title = 'Oxygen Builder Reusable Parts';
	protected static $description = 'Oxygen Builder Reusable Parts issues found';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Oxygen Builder
		if ( ! defined( 'CT_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Count reusable parts
		$reusable_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_type = %s AND post_status = %s",
				'ct_template',
				'publish'
			)
		);
		
		if ( $reusable_count === 0 ) {
			return null;
		}
		
		// Check 2: Excessive reusable parts
		if ( $reusable_count > 50 ) {
			$issues[] = sprintf( __( '%d reusable parts (management overhead)', 'wpshadow' ), $reusable_count );
		}
		
		// Check 3: Global styles usage
		$global_styles = get_option( 'oxygen_global_styles', array() );
		if ( empty( $global_styles ) && $reusable_count > 10 ) {
			$issues[] = __( 'No global styles (inconsistent styling)', 'wpshadow' );
		}
		
		// Check 4: Unused reusable parts
		$used_templates = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT meta_value FROM {$wpdb->postmeta} 
				WHERE meta_key = %s",
				'ct_template_ref'
			)
		);
		
		$unused = $reusable_count - count( array_unique( $used_templates ) );
		if ( $unused > 5 ) {
			$issues[] = sprintf( __( '%d unused reusable parts (database bloat)', 'wpshadow' ), $unused );
		}
		
		// Check 5: Conditional display complexity
		$complex_conditions = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} 
				WHERE meta_key = %s AND LENGTH(meta_value) > 1000",
				'ct_conditions'
			)
		);
		
		if ( $complex_conditions > 10 ) {
			$issues[] = sprintf( __( '%d parts with complex conditions (performance impact)', 'wpshadow' ), $complex_conditions );
		}
		
		// Check 6: Revision control
		$revisions_enabled = get_option( 'oxygen_revisions', 'on' );
		if ( 'off' === $revisions_enabled ) {
			$issues[] = __( 'Revisions disabled (no change history)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of reusable parts issues */
				__( 'Oxygen Builder reusable parts have %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/oxygen-builder-reusable-parts',
		);
	}
}
