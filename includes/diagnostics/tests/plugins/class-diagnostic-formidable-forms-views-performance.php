<?php
/**
 * Formidable Forms Views Diagnostic
 *
 * Formidable Forms views not cached.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.265.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formidable Forms Views Diagnostic Class
 *
 * @since 1.265.0000
 */
class Diagnostic_FormidableFormsViewsPerformance extends Diagnostic_Base {

	protected static $slug = 'formidable-forms-views-performance';
	protected static $title = 'Formidable Forms Views';
	protected static $description = 'Formidable Forms views not cached';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'FrmAppHelper' ) ) {
			return null;
		}

		global $wpdb;
		$issues = array();

		// Check 1: View count
		$view_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}frm_views"
		);
		if ( $view_count > 50 ) {
			$issues[] = sprintf( __( '%d views defined (query overhead)', 'wpshadow' ), $view_count );
		}

		// Check 2: View caching
		$caching = get_option( 'frm_view_caching', 'no' );
		if ( 'no' === $caching ) {
			$issues[] = __( 'Views not cached (repeated queries)', 'wpshadow' );
		}

		// Check 3: Entries per view
		$max_entries = $wpdb->get_var(
			"SELECT MAX(frm_limit) FROM {$wpdb->prefix}frm_views"
		);
		if ( $max_entries > 100 ) {
			$issues[] = sprintf( __( '%d max entries per view (slow loading)', 'wpshadow' ), $max_entries );
		}

		// Check 4: Pagination
		$no_pagination = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}frm_views WHERE frm_page_size = 0"
		);
		if ( $no_pagination > 0 ) {
			$issues[] = sprintf( __( '%d views without pagination (memory)', 'wpshadow' ), $no_pagination );
		}

		// Check 5: Complex calculations
		$calculations = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}frm_views WHERE frm_options LIKE '%calculation%'"
		);
		if ( $calculations > 10 ) {
			$issues[] = sprintf( __( '%d views with calculations (CPU intensive)', 'wpshadow' ), $calculations );
		}

		// Check 6: AJAX loading
		$ajax = get_option( 'frm_view_ajax', 'no' );
		if ( 'no' === $ajax ) {
			$issues[] = __( 'No AJAX loading (slow page rendering)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 35;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 47;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 41;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Formidable Forms views have %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/formidable-forms-views-performance',
		);
	}
}
