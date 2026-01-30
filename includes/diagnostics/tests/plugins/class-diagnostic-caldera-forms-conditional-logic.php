<?php
/**
 * Caldera Forms Conditional Logic Diagnostic
 *
 * Caldera Forms logic slowing forms.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.474.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Caldera Forms Conditional Logic Diagnostic Class
 *
 * @since 1.474.0000
 */
class Diagnostic_CalderaFormsConditionalLogic extends Diagnostic_Base {

	protected static $slug = 'caldera-forms-conditional-logic';
	protected static $title = 'Caldera Forms Conditional Logic';
	protected static $description = 'Caldera Forms logic slowing forms';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Caldera_Forms' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check for forms with conditional logic
		global $wpdb;
		$forms_table = $wpdb->prefix . 'cf_forms';
		
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$forms_table}'" ) === $forms_table ) {
			$conditional_forms = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$forms_table} WHERE form_config LIKE '%conditional%'"
			);
			
			if ( $conditional_forms > 0 ) {
				// Check for complex nested conditions
				$nested_conditions = $wpdb->get_var(
					"SELECT COUNT(*) FROM {$forms_table} 
					 WHERE form_config LIKE '%conditional%' 
					 AND (form_config LIKE '%and%' OR form_config LIKE '%or%')"
				);
				
				if ( $nested_conditions > 5 ) {
					$issues[] = "excessive conditional logic rules ({$nested_conditions} forms with complex conditions)";
				}
			}
		}
		
		// Check if JavaScript optimization is enabled
		$js_optimize = get_option( 'cf_js_optimize', '0' );
		if ( '0' === $js_optimize ) {
			$issues[] = 'JavaScript optimization disabled for conditional logic';
		}
		
		// Check for AJAX submission with conditionals
		$ajax_enabled = get_option( 'cf_ajax_enabled', '1' );
		if ( '0' === $ajax_enabled && $conditional_forms > 0 ) {
			$issues[] = 'AJAX disabled (conditional logic requires full page reloads)';
		}
		
		// Check for inline rendering of conditional fields
		$inline_render = get_option( 'cf_inline_conditional_render', '1' );
		if ( '0' === $inline_render ) {
			$issues[] = 'inline conditional rendering disabled (impacts performance)';
		}
		
		// Check for caching conflicts
		$cache_enabled = get_option( 'cf_cache_forms', '0' );
		if ( '1' === $cache_enabled && $conditional_forms > 0 ) {
			$issues[] = 'form caching enabled with conditional logic (may cause display issues)';
		}
		
		// Check for form submission errors from conditionals
		$error_logs = get_transient( 'cf_conditional_errors' );
		if ( false !== $error_logs && is_array( $error_logs ) && count( $error_logs ) > 10 ) {
			$issues[] = 'multiple conditional logic errors detected (' . count( $error_logs ) . ' recent errors)';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Caldera Forms conditional logic performance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/caldera-forms-conditional-logic',
			);
		}
		
		return null;
	}
}
