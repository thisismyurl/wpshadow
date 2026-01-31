<?php
/**
 * Metabox Io Conditional Logic Diagnostic
 *
 * Metabox Io Conditional Logic issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1060.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Metabox Io Conditional Logic Diagnostic Class
 *
 * @since 1.1060.0000
 */
class Diagnostic_MetaboxIoConditionalLogic extends Diagnostic_Base {

	protected static $slug = 'metabox-io-conditional-logic';
	protected static $title = 'Metabox Io Conditional Logic';
	protected static $description = 'Metabox Io Conditional Logic issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Meta Box plugin
		if ( ! function_exists( 'rwmb_meta' ) && ! defined( 'RWMB_VER' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Conditional Logic extension active
		$conditional_active = defined( 'MBCL_VER' ) || class_exists( 'MB_Conditional_Logic_Parser' );
		if ( ! $conditional_active ) {
			return null;
		}
		
		// Check 2: Meta boxes with conditional logic
		$meta_boxes = apply_filters( 'rwmb_meta_boxes', array() );
		$conditional_count = 0;
		$complex_count = 0;
		
		foreach ( $meta_boxes as $meta_box ) {
			if ( isset( $meta_box['fields'] ) ) {
				foreach ( $meta_box['fields'] as $field ) {
					if ( isset( $field['visible'] ) || isset( $field['hidden'] ) ) {
						$conditional_count++;
						
						// Check for complex nested conditions
						$conditions = isset( $field['visible'] ) ? $field['visible'] : $field['hidden'];
						if ( is_array( $conditions ) && count( $conditions ) > 5 ) {
							$complex_count++;
						}
					}
				}
			}
		}
		
		if ( $conditional_count === 0 ) {
			return null;
		}
		
		// Check 3: Complex conditional logic
		if ( $complex_count > 5 ) {
			$issues[] = sprintf( __( '%d fields with complex conditions (performance impact)', 'wpshadow' ), $complex_count );
		}
		
		// Check 4: JavaScript validation
		$js_validation = get_option( 'rwmb_conditional_logic_validation', true );
		if ( ! $js_validation ) {
			$issues[] = __( 'Client-side validation disabled (UX degraded)', 'wpshadow' );
		}
		
		// Check 5: Dependency caching
		$cache_dependencies = get_option( 'rwmb_cache_field_dependencies', false );
		if ( ! $cache_dependencies && $conditional_count > 20 ) {
			$issues[] = __( 'Field dependency caching disabled (slow edit screens)', 'wpshadow' );
		}
		
		// Check 6: Excessive conditional fields
		if ( $conditional_count > 50 ) {
			$issues[] = sprintf( __( '%d fields with conditional logic (consider optimization)', 'wpshadow' ), $conditional_count );
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
				/* translators: %s: list of conditional logic issues */
				__( 'Meta Box conditional logic has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/metabox-io-conditional-logic',
		);
	}
}
