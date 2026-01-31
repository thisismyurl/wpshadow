<?php
/**
 * Formidable Forms Calculated Fields Diagnostic
 *
 * Formidable Forms Calculated Fields issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1195.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formidable Forms Calculated Fields Diagnostic Class
 *
 * @since 1.1195.0000
 */
class Diagnostic_FormidableFormsCalculatedFields extends Diagnostic_Base {

	protected static $slug = 'formidable-forms-calculated-fields';
	protected static $title = 'Formidable Forms Calculated Fields';
	protected static $description = 'Formidable Forms Calculated Fields issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'FrmAppHelper' ) ) {
			return null;
		}

		$issues = array();

		// Check for forms with calculated fields
		global $wpdb;
		$calc_fields = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}frm_fields WHERE type = %s",
				'number'
			)
		);

		if ( $calc_fields > 0 ) {
			// Check for complex calculation formulas
			$complex_calcs = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}frm_fields WHERE type = %s AND field_options LIKE %s",
					'number',
					'%calc%'
				)
			);

			if ( $complex_calcs > 5 ) {
				$issues[] = "excessive calculated fields ({$complex_calcs} found, impacts performance)";
			}

			// Check for JavaScript calculation errors
			$js_errors = get_transient( 'frm_calc_field_errors' );
			if ( false !== $js_errors && $js_errors > 0 ) {
				$issues[] = 'JavaScript calculation errors detected';
			}

			// Check for circular dependencies in calculations
			$circular_refs = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->prefix}frm_fields f1
				 JOIN {$wpdb->prefix}frm_fields f2 ON f1.form_id = f2.form_id
				 WHERE f1.type = 'number' AND f2.type = 'number'
				 AND f1.field_options LIKE CONCAT('%[', f2.id, ']%')
				 AND f2.field_options LIKE CONCAT('%[', f1.id, ']%')"
			);

			if ( $circular_refs > 0 ) {
				$issues[] = 'circular field references in calculations';
			}

			// Check for calculation cache issues
			$cache_enabled = get_option( 'frm_cache_enabled', '0' );
			if ( '0' === $cache_enabled && $calc_fields > 10 ) {
				$issues[] = 'calculation caching disabled with many calculated fields';
			}

			// Check for decimal precision issues
			$precision_issues = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}frm_fields
					 WHERE type = %s AND field_options LIKE %s AND field_options NOT LIKE %s",
					'number',
					'%calc%',
					'%decimal%'
				)
			);

			if ( $precision_issues > 0 ) {
				$issues[] = 'calculated fields without decimal precision settings';
			}
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 8 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Formidable Forms calculated field issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/formidable-forms-calculated-fields',
			);
		}

		return null;
	}
}
