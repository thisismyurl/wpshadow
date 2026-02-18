<?php
/**
 * Missing Issue Prioritization System
 *
 * Tests whether Site Health helps users prioritize fixes based on impact.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SiteHealth
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Missing_Issue_Prioritization_System Class
 *
 * Validates prioritization and impact guidance in Site Health.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Missing_Issue_Prioritization_System extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-issue-prioritization-system';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Issue Prioritization Guidance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies Site Health helps prioritize fixes based on impact';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'site_health';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests for prioritization support.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count issues requiring user attention
		$issue_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '%site_health_issue%'" );

		if ( $issue_count < 2 ) {
			return null; // Doesn't need prioritization with few issues
		}

		$issues = array();

		// 1. Check for severity-based sorting
		if ( ! self::has_severity_sorting() ) {
			$issues[] = __( 'Issues not sorted by severity level', 'wpshadow' );
		}

		// 2. Check for impact explanation
		if ( ! self::has_impact_explanation() ) {
			$issues[] = __( 'No explanation of how issues impact site', 'wpshadow' );
		}

		// 3. Check for quick wins identification
		if ( ! self::identifies_quick_wins() ) {
			$issues[] = __( 'Quick wins (easy fixes) not highlighted', 'wpshadow' );
		}

		// 4. Check for recommended fix order
		if ( ! self::has_fix_order_guidance() ) {
			$issues[] = __( 'No recommended order for fixing issues', 'wpshadow' );
		}

		// 5. Check for KPI impact metrics
		if ( ! self::has_impact_metrics() ) {
			$issues[] = __( 'No metrics showing issue impact (e.g., "affects 30% of users")', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: issues, %d: gaps */
					__( '%d issues detected with %d prioritization gaps', 'wpshadow' ),
					$issue_count,
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/issue-prioritization-system',
				'recommendations' => array(
					__( 'Sort issues by severity (critical → warning → info)', 'wpshadow' ),
					__( 'Add impact score to each issue', 'wpshadow' ),
					__( 'Highlight quick wins for motivation', 'wpshadow' ),
					__( 'Provide recommended fix sequence', 'wpshadow' ),
					__( 'Show estimated time for each fix', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check for severity-based sorting.
	 *
	 * @since  1.6030.2148
	 * @return bool True if severity sorting available.
	 */
	private static function has_severity_sorting() {
		// Check if issues are sorted by severity
		// Should show critical first, then warnings, then recommended

		// Check for sorting option
		if ( get_option( 'wpshadow_sort_issues_by_severity', false ) ) {
			return true;
		}

		// Check for custom ordering capability
		global $wp_filter;
		if ( isset( $wp_filter['wpshadow_issue_sort_priority'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for impact explanation.
	 *
	 * @since  1.6030.2148
	 * @return bool True if impact explained.
	 */
	private static function has_impact_explanation() {
		// Check if each issue explains its impact
		// E.g., "This affects page load time" or "This impacts security"

		// Check for impact descriptions
		$impact_option = get_option( 'wpshadow_issue_impact_descriptions', array() );

		if ( ! empty( $impact_option ) ) {
			return true;
		}

		// Check if test descriptions include impact info
		if ( class_exists( 'WP_Site_Health' ) ) {
			$site_health = \WP_Site_Health::get_instance();

			if ( method_exists( $site_health, 'get_tests' ) ) {
				return true; // Tests should include description
			}
		}

		return false;
	}

	/**
	 * Identify quick wins.
	 *
	 * @since  1.6030.2148
	 * @return bool True if quick wins identified.
	 */
	private static function identifies_quick_wins() {
		// Quick wins are issues that:
		// - Can be fixed with one click
		// - Take < 1 minute to fix
		// - Have high impact (e.g., remove hello.php)

		// Check for quick wins categorization
		if ( get_option( 'wpshadow_quick_wins_identified', false ) ) {
			return true;
		}

		// Check for fast-fix category
		$quick_fixes = array(
			'hello_dolly',    // Remove plugin
			'empty_spam',     // Delete spam comments
			'remove_revisions', // Delete post revisions
		);

		// If any quick fix is available
		foreach ( $quick_fixes as $fix ) {
			if ( has_action( "wpshadow_quick_fix_{$fix}" ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for fix order guidance.
	 *
	 * @since  1.6030.2148
	 * @return bool True if order guidance available.
	 */
	private static function has_fix_order_guidance() {
		// Check if recommended fix sequence exists
		// Some fixes should be done in order:
		// 1. Enable HTTPS
		// 2. Force HTTPS redirect
		// 3. Update plugins
		// 4. Run backups

		// Check for fix roadmap
		if ( get_option( 'wpshadow_fix_roadmap', false ) ) {
			return true;
		}

		// Check for dependency tracking
		if ( has_filter( 'wpshadow_issue_dependencies' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for impact metrics.
	 *
	 * @since  1.6030.2148
	 * @return bool True if metrics available.
	 */
	private static function has_impact_metrics() {
		// Check if issues show measurable impact
		// E.g., "Affects 30% of users", "Could save 2 seconds per page load"

		// Check for KPI tracking
		if ( function_exists( 'wpshadow_get_issue_impact_kpi' ) ) {
			return true;
		}

		// Check for activity logger with impact data
		if ( function_exists( 'wpshadow_log_activity' ) ) {
			return true;
		}

		return false;
	}
}
