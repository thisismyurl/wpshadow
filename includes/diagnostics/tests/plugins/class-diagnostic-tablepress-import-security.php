<?php
/**
 * TablePress Import Security Diagnostic
 *
 * TablePress imports not validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.416.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TablePress Import Security Diagnostic Class
 *
 * @since 1.416.0000
 */
class Diagnostic_TablepressImportSecurity extends Diagnostic_Base {

	protected static $slug = 'tablepress-import-security';
	protected static $title = 'TablePress Import Security';
	protected static $description = 'TablePress imports not validated';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'TABLEPRESS_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Tables exist
		$table_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'tablepress_table'
			)
		);
		
		if ( $table_count === 0 ) {
			return null;
		}
		
		// Check 2: HTML in table cells
		$html_sanitization = get_option( 'tablepress_sanitize_html', true );
		if ( ! $html_sanitization ) {
			$issues[] = __( 'HTML sanitization disabled (XSS vulnerability)', 'wpshadow' );
		}
		
		// Check 3: Import file size limit
		$import_limit = get_option( 'tablepress_import_max_filesize', 2048 ); // KB
		if ( $import_limit > 5120 ) { // 5MB
			$issues[] = sprintf( __( 'Import limit: %s (DoS risk)', 'wpshadow' ), size_format( $import_limit * 1024 ) );
		}
		
		// Check 4: JavaScript in table cells
		$allow_js = get_option( 'tablepress_allow_javascript', false );
		if ( $allow_js ) {
			$issues[] = __( 'JavaScript allowed in tables (critical XSS risk)', 'wpshadow' );
		}
		
		// Check 5: Import capability check
		$import_capability = get_option( 'tablepress_import_capability', 'edit_tables' );
		if ( 'edit_posts' === $import_capability ) {
			$issues[] = __( 'Contributors can import tables (privilege escalation)', 'wpshadow' );
		}
		
		// Check 6: Formula evaluation
		$evaluate_formulas = get_option( 'tablepress_evaluate_formulas', false );
		if ( $evaluate_formulas ) {
			$issues[] = __( 'Formula evaluation enabled (code execution risk)', 'wpshadow' );
		}
		
		// Check 7: Export accessibility
		$public_export = get_option( 'tablepress_allow_public_export', false );
		if ( $public_export ) {
			$issues[] = __( 'Public table export enabled (data exposure)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 60;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 78;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 69;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of security issues */
				__( 'TablePress import security has %d critical issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/tablepress-import-security',
		);
	}
}
