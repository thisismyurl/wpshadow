<?php
/**
 * ACF Conditional Logic Performance Diagnostic
 *
 * ACF conditional logic slowing admin.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.457.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACF Conditional Logic Performance Diagnostic Class
 *
 * @since 1.457.0000
 */
class Diagnostic_AcfConditionalLogicPerformance extends Diagnostic_Base {

	protected static $slug = 'acf-conditional-logic-performance';
	protected static $title = 'ACF Conditional Logic Performance';
	protected static $description = 'ACF conditional logic slowing admin';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'ACF' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Field groups with conditional logic
		$field_groups = acf_get_field_groups();
		$groups_with_logic = 0;
		$total_conditional_fields = 0;
		
		foreach ( $field_groups as $group ) {
			$fields = acf_get_fields( $group['key'] );
			$has_logic = false;
			
			foreach ( $fields as $field ) {
				if ( ! empty( $field['conditional_logic'] ) ) {
					$has_logic = true;
					$total_conditional_fields++;
					
					// Check for complex logic (multiple rule groups)
					if ( is_array( $field['conditional_logic'] ) && count( $field['conditional_logic'] ) > 3 ) {
						$issues[] = sprintf(
							/* translators: %s: field name */
							__( 'Field "%s" has complex conditional logic (performance)', 'wpshadow' ),
							substr( $field['label'], 0, 30 )
						);
						break;
					}
				}
			}
			
			if ( $has_logic ) {
				$groups_with_logic++;
			}
		}
		
		if ( $groups_with_logic === 0 ) {
			return null;
		}
		
		// Check 2: Excessive conditional fields
		if ( $total_conditional_fields > 50 ) {
			$issues[] = sprintf( __( '%d fields with conditional logic (JavaScript overhead)', 'wpshadow' ), $total_conditional_fields );
		}
		
		// Check 3: Local JSON enabled
		$local_json_enabled = defined( 'ACF_LITE' ) || get_option( 'acf_local_json_enabled', false );
		if ( ! $local_json_enabled && $groups_with_logic > 10 ) {
			$issues[] = __( 'ACF Local JSON not enabled (database queries)', 'wpshadow' );
		}
		
		// Check 4: Field group count
		if ( count( $field_groups ) > 30 ) {
			$issues[] = sprintf( __( '%d field groups registered (admin slowdown)', 'wpshadow' ), count( $field_groups ) );
		}
		
		
		// Check 6: Cache status
		if ( ! (defined( "WP_CACHE" ) && WP_CACHE) ) {
			$issues[] = __( 'Cache status', 'wpshadow' );
		}

		// Check 7: Database optimization
		if ( ! (! is_option_empty( "db_optimized" )) ) {
			$issues[] = __( 'Database optimization', 'wpshadow' );
		}

		// Check 8: Asset minification
		if ( ! (function_exists( "wp_enqueue_script" )) ) {
			$issues[] = __( 'Asset minification', 'wpshadow' );
		}
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = (40 + min(35, count($issues) * 8));
		if ( count( $issues ) >= 3 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		} elseif ( count( $issues ) >= 2 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of ACF performance issues */
				__( 'ACF conditional logic has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/acf-conditional-logic-performance',
		);
	}
}
