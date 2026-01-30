<?php
/**
 * Accessibility Checker Wcag Level Diagnostic
 *
 * Accessibility Checker Wcag Level not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1136.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Checker Wcag Level Diagnostic Class
 *
 * @since 1.1136.0000
 */
class Diagnostic_AccessibilityCheckerWcagLevel extends Diagnostic_Base {

	protected static $slug = 'accessibility-checker-wcag-level';
	protected static $title = 'Accessibility Checker Wcag Level';
	protected static $description = 'Accessibility Checker Wcag Level not compliant';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'Accessibility_Checker' ) && ! defined( 'EDAC_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: WCAG level configuration.
		$wcag_level = get_option( 'edac_wcag_level', '' );
		if ( empty( $wcag_level ) ) {
			$issues[] = 'WCAG compliance level not set';
		} elseif ( ! in_array( $wcag_level, array( 'A', 'AA', 'AAA' ), true ) ) {
			$issues[] = "invalid WCAG level configured ({$wcag_level})";
		}

		// Check 2: WCAG version being checked.
		$wcag_version = get_option( 'edac_wcag_version', '2.1' );
		if ( version_compare( $wcag_version, '2.1', '<' ) ) {
			$issues[] = "outdated WCAG version {$wcag_version} (use 2.1 or higher)";
		}

		// Check 3: Total violations by level.
		global $wpdb;
		$level_a_violations = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}accessibility_checker WHERE wcag_level = %s AND status = %s",
				'A',
				'failed'
			)
		);
		if ( $level_a_violations > 0 ) {
			$issues[] = "{$level_a_violations} Level A violations (critical for basic accessibility)";
		}

		// Check 4: Level AA violations.
		$level_aa_violations = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}accessibility_checker WHERE wcag_level = %s AND status = %s",
				'AA',
				'failed'
			)
		);
		if ( $level_aa_violations > 0 && 'AA' === $wcag_level ) {
			$issues[] = "{$level_aa_violations} Level AA violations (legal requirement in many regions)";
		}

		// Check 5: Ignored violations.
		$ignored_violations = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}accessibility_checker WHERE status = 'ignored'"
		);
		if ( $ignored_violations > 10 ) {
			$issues[] = "{$ignored_violations} violations marked as ignored (may indicate unresolved issues)";
		}

		// Check 6: Compliance score trend.
		$current_score = get_option( 'edac_compliance_score', 0 );
		$previous_score = get_option( 'edac_compliance_score_previous', 0 );
		if ( $current_score < $previous_score && $current_score < 80 ) {
			$issues[] = "compliance score declining ({$current_score}% down from {$previous_score}%)";
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Accessibility Checker WCAG compliance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/accessibility-checker-wcag-level',
			);
		}

		return null;
	}
}
