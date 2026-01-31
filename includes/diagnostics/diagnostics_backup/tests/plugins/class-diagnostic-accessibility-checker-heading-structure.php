<?php
/**
 * Accessibility Checker Heading Structure Diagnostic
 *
 * Accessibility Checker Heading Structure not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1138.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Checker Heading Structure Diagnostic Class
 *
 * @since 1.1138.0000
 */
class Diagnostic_AccessibilityCheckerHeadingStructure extends Diagnostic_Base {

	protected static $slug = 'accessibility-checker-heading-structure';
	protected static $title = 'Accessibility Checker Heading Structure';
	protected static $description = 'Accessibility Checker Heading Structure not compliant';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'Accessibility_Checker' ) && ! defined( 'EDAC_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Heading structure checking enabled.
		$heading_check = get_option( 'edac_heading_check_enabled', '1' );
		if ( '0' === $heading_check ) {
			$issues[] = 'heading structure checking disabled';
		}

		// Check 2: Pages with skipped heading levels.
		global $wpdb;
		$skipped_headings = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}accessibility_checker WHERE check_type = %s AND issue_type = %s",
				'heading_structure',
				'skipped_level'
			)
		);
		if ( $skipped_headings > 0 ) {
			$issues[] = "{$skipped_headings} instances of skipped heading levels (H1 to H3, etc.)";
		}

		// Check 3: Pages missing H1 tags.
		$missing_h1 = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}accessibility_checker WHERE check_type = %s AND issue_type = %s",
				'heading_structure',
				'missing_h1'
			)
		);
		if ( $missing_h1 > 0 ) {
			$issues[] = "{$missing_h1} pages missing H1 heading";
		}

		// Check 4: Pages with multiple H1 tags.
		$multiple_h1 = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}accessibility_checker WHERE check_type = %s AND issue_type = %s",
				'heading_structure',
				'multiple_h1'
			)
		);
		if ( $multiple_h1 > 0 ) {
			$issues[] = "{$multiple_h1} pages with multiple H1 tags (confuses screen readers)";
		}

		// Check 5: Empty headings detected.
		$empty_headings = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}accessibility_checker WHERE check_type = %s AND issue_type = %s",
				'heading_structure',
				'empty_heading'
			)
		);
		if ( $empty_headings > 0 ) {
			$issues[] = "{$empty_headings} empty heading tags found";
		}

		// Check 6: Pages not scanned recently.
		$total_pages = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type IN ('post', 'page') AND post_status = 'publish'"
		);
		$scanned_pages = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->prefix}accessibility_checker WHERE check_type = %s AND last_checked > %s",
				'heading_structure',
				date( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			)
		);
		if ( $scanned_pages < ( $total_pages * 0.8 ) ) {
			$percentage = round( ( $scanned_pages / $total_pages ) * 100 );
			$issues[] = "only {$percentage}% of pages scanned in last 30 days";
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Accessibility Checker heading structure issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/accessibility-checker-heading-structure',
			);
		}

		return null;
	}
}
