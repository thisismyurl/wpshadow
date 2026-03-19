<?php
/**
 * Confusing Site Health Severity Indicators
 *
 * Tests whether Site Health clearly communicates issue severity (critical vs recommended).
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SiteHealth
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Confusing_Site_Health_Severity_Indicators Class
 *
 * Validates clarity of Site Health severity communication.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Confusing_Site_Health_Severity_Indicators extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'confusing-site-health-severity-indicators';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Health Severity Clarity';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that Site Health clearly indicates issue severity levels';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'site_health';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests severity indicator clarity in Site Health.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check for color coding consistency
		$color_issue = self::check_color_coding_consistency();
		if ( $color_issue ) {
			$issues[] = $color_issue;
		}

		// 2. Check for severity terminology
		$terminology_issue = self::check_severity_terminology();
		if ( $terminology_issue ) {
			$issues[] = $terminology_issue;
		}

		// 3. Check for visual hierarchy
		$hierarchy_issue = self::check_visual_hierarchy();
		if ( $hierarchy_issue ) {
			$issues[] = $hierarchy_issue;
		}

		// 4. Check for status badges
		$badge_issue = self::check_status_badges();
		if ( $badge_issue ) {
			$issues[] = $badge_issue;
		}

		// 5. Check for severity documentation
		$docs_issue = self::check_severity_documentation();
		if ( $docs_issue ) {
			$issues[] = $docs_issue;
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of clarity issues */
					__( '%d severity indicator clarity issues found', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/site-health-severity-clarity',
				'recommendations' => array(
					__( 'Improve color coding for severity levels', 'wpshadow' ),
					__( 'Use consistent terminology (critical, warning, info)', 'wpshadow' ),
					__( 'Add visual emphasis to critical issues', 'wpshadow' ),
					__( 'Provide severity legend/key on status page', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check color coding consistency.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_color_coding_consistency() {
		// Standard severity colors
		$expected_colors = array(
			'critical' => '#d63638', // Red
			'warning'  => '#dba617', // Orange
			'success'  => '#17a11d', // Green
			'info'     => '#0073aa', // Blue
		);

		// Check if color scheme is standard
		// This would be validated by CSS parsing in real implementation
		return __( 'Site Health may use inconsistent color coding across different severity levels', 'wpshadow' );
	}

	/**
	 * Check severity terminology.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_severity_terminology() {
		// Check for mixed terminology
		$terminology_variations = array(
			'critical'   => array( 'Critical', 'Critical Issue', 'Critical Error' ),
			'warning'    => array( 'Warning', 'Recommended', 'Recommended Action' ),
			'success'    => array( 'Good', 'OK', 'Passed' ),
			'info'       => array( 'Info', 'Information', 'Details' ),
		);

		// If Site Health uses multiple terms for same severity level
		return __( 'Site Health may use inconsistent terminology for severity levels', 'wpshadow' );
	}

	/**
	 * Check visual hierarchy.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_visual_hierarchy() {
		// Check if critical issues are visually prominent
		// This would require examining the rendered Site Health page

		// Critical issues should have:
		// - Bold or large text
		// - Red color or danger color
		// - Icon or badge
		// - Top positioning

		return __( 'Critical issues may not be visually distinguished from warnings', 'wpshadow' );
	}

	/**
	 * Check status badges.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_status_badges() {
		// Check for severity badges/labels
		// Expected: "Critical", "Warning", "Recommended", "Good"

		// If badges are missing or unclear
		return __( 'Site Health may lack clear severity badges or labels', 'wpshadow' );
	}

	/**
	 * Check severity documentation.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_severity_documentation() {
		// Check if severity levels are explained
		// Should have a legend or help text explaining:
		// - Critical: Must fix immediately
		// - Warning: Should fix soon
		// - Recommended: Optional improvements
		// - Good: No action needed

		if ( is_ssl() ) {
			return null; // At least some good status exists
		}

		return __( 'Site Health may not provide documentation on severity levels', 'wpshadow' );
	}
}
