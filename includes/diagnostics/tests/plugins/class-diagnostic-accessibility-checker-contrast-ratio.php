<?php
/**
 * Accessibility Checker Contrast Ratio Diagnostic
 *
 * Accessibility Checker Contrast Ratio not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1137.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Checker Contrast Ratio Diagnostic Class
 *
 * @since 1.1137.0000
 */
class Diagnostic_AccessibilityCheckerContrastRatio extends Diagnostic_Base {

	protected static $slug = 'accessibility-checker-contrast-ratio';
	protected static $title = 'Accessibility Checker Contrast Ratio';
	protected static $description = 'Accessibility Checker Contrast Ratio not compliant';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'Accessibility_Checker' ) && ! defined( 'EDAC_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Contrast ratio checking enabled.
		$contrast_check = get_option( 'edac_contrast_check_enabled', '1' );
		if ( '0' === $contrast_check ) {
			$issues[] = 'contrast ratio checking disabled';
		}
		
		// Check 2: Failed contrast checks in database.
		global $wpdb;
		$failed_checks = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}accessibility_checker WHERE check_type = %s AND status = %s",
				'contrast_ratio',
				'failed'
			)
		);
		if ( $failed_checks > 0 ) {
			$issues[] = "{$failed_checks} contrast ratio violations detected";
		}
		
		// Check 3: Minimum contrast level setting.
		$min_contrast = get_option( 'edac_min_contrast_ratio', '4.5' );
		if ( floatval( $min_contrast ) < 4.5 ) {
			$issues[] = "minimum contrast ratio set to {$min_contrast} (WCAG AA requires 4.5:1)";
		}
		
		// Check 4: Pages with contrast issues.
		$pages_with_issues = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->prefix}accessibility_checker WHERE check_type = %s AND status = %s",
				'contrast_ratio',
				'failed'
			)
		);
		if ( $pages_with_issues > 0 ) {
			$issues[] = "{$pages_with_issues} pages with contrast violations";
		}
		
		// Check 5: Auto-scan on publish.
		$auto_scan = get_option( 'edac_auto_scan_on_publish', '1' );
		if ( '0' === $auto_scan ) {
			$issues[] = 'automatic scanning on publish disabled (new content not checked)';
		}
		
		// Check 6: Color scheme compatibility.
		$color_schemes = get_option( 'edac_check_color_schemes', array() );
		if ( empty( $color_schemes ) ) {
			$issues[] = 'no color schemes configured for testing (may miss dark mode issues)';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Accessibility Checker contrast issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/accessibility-checker-contrast-ratio',
			);
		}
		
		return null;
	}
}
