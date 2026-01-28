<?php
/**
 * [Diagnostic Name] Diagnostic
 *
 * [Detailed description of what this diagnostic checks and why it matters.
 * Include the user impact and business value.]
 *
 * @since   1.YDDD.HHMM
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_[ClassName] Class
 *
 * [Detailed class description explaining detection method and approach]
 *
 * @since 1.YDDD.HHMM
 */
class Diagnostic_[ClassName] extends Diagnostic_Base {

	/**
	 * The diagnostic slug (kebab-case identifier)
	 *
	 * @var string
	 */
	protected static $slug = '[kebab-case-slug]';

	/**
	 * The diagnostic title (human-readable)
	 *
	 * @var string
	 */
	protected static $title = '[Human Readable Title]';

	/**
	 * The diagnostic description (brief explanation)
	 *
	 * @var string
	 */
	protected static $description = '[Brief description of what this checks]';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = '[category]'; // security, performance, html_seo, admin, etc.

	/**
	 * Run the diagnostic check.
	 *
	 * Implements the core detection logic. Returns null if no issue found,
	 * or a comprehensive finding array if an issue is detected.
	 *
	 * @since  1.YDDD.HHMM
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Step 1: Early bailout checks (avoid expensive operations if not needed)
		if ( ! self::should_run_check() ) {
			return null;
		}

		// Step 2: Gather data to analyze
		$data = self::gather_diagnostic_data();

		// Step 3: Analyze the data
		$issue_detected = self::analyze_data( $data );

		// Step 4: If no issue, return null
		if ( ! $issue_detected ) {
			return null;
		}

		// Step 5: Return comprehensive finding with all required keys
		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %s: specific detail about the issue */
				__( '[Specific description with details: %s]', 'wpshadow' ),
				$data['specific_detail']
			),
			'severity'      => 'medium', // critical (80-100), high (60-79), medium (40-59), low (20-39), info (0-19)
			'threat_level'  => 50, // 0-100 numeric score matching severity
			'auto_fixable'  => false, // true if can be auto-fixed
			'kb_link'       => 'https://wpshadow.com/kb/' . self::$slug,
			'family'        => self::$family,
			'meta'          => array(
				// Contextual data for display
				'affected_count'   => $data['count'],
				'current_value'    => $data['current'],
				'recommended'      => $data['recommended'],
				'impact_level'     => __( 'Medium - [Specific impact]', 'wpshadow' ),
				'immediate_actions' => array(
					__( 'Action 1: [First step]', 'wpshadow' ),
					__( 'Action 2: [Second step]', 'wpshadow' ),
					__( 'Action 3: [Third step]', 'wpshadow' ),
				),
			),
			'details'       => array(
				// Comprehensive fix instructions (optional, use for complex issues)
				'why_important'    => __( '[Explain consequences and business impact]', 'wpshadow' ),
				'user_impact'      => array(
					__( 'UX Impact: [How users are affected]' ),
					__( 'SEO Impact: [How search rankings are affected]' ),
					__( 'Conversion Impact: [How sales/signups are affected]' ),
				),
				'solution_options' => array(
					'Option 1: [Quick Fix]' => array(
						'description' => __( '[How to implement]', 'wpshadow' ),
						'time'        => __( '[e.g., 5 minutes]', 'wpshadow' ),
						'cost'        => __( '[e.g., Free]', 'wpshadow' ),
						'difficulty'  => __( '[Easy/Medium/Advanced]', 'wpshadow' ),
					),
					'Option 2: [Plugin Solution]' => array(
						'description' => __( '[Plugin name and setup]', 'wpshadow' ),
						'time'        => __( '[e.g., 10 minutes]', 'wpshadow' ),
						'cost'        => __( '[e.g., $0-50/year]', 'wpshadow' ),
						'plugin'      => '[plugin-slug]',
					),
					'Option 3: [Professional Solution]' => array(
						'description' => __( '[Hire developer or service]', 'wpshadow' ),
						'time'        => __( '[e.g., 1-2 hours]', 'wpshadow' ),
						'cost'        => __( '[e.g., $100-500]', 'wpshadow' ),
					),
				),
				'best_practices'   => array(
					__( 'Best practice 1', 'wpshadow' ),
					__( 'Best practice 2', 'wpshadow' ),
					__( 'Best practice 3', 'wpshadow' ),
				),
				'testing_steps'    => array(
					'Step 1' => __( '[How to verify the issue]', 'wpshadow' ),
					'Step 2' => __( '[How to implement fix]', 'wpshadow' ),
					'Step 3' => __( '[How to verify fix worked]', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Check if this diagnostic should run.
	 *
	 * Implements early bailout logic to avoid expensive operations
	 * when they're not needed.
	 *
	 * @since  1.YDDD.HHMM
	 * @return bool True if check should run, false otherwise.
	 */
	private static function should_run_check() {
		// Example bailout conditions:
		// - Not on admin pages
		// - Feature is disabled
		// - Required functionality not available

		if ( is_admin() ) {
			return false; // Or true, depending on diagnostic
		}

		// Add other conditions as needed
		return true;
	}

	/**
	 * Gather data needed for diagnostic analysis.
	 *
	 * Collects all relevant data points that will be analyzed
	 * to determine if an issue exists.
	 *
	 * @since  1.YDDD.HHMM
	 * @return array Data array with all needed information.
	 */
	private static function gather_diagnostic_data() {
		$data = array(
			'count'           => 0,
			'current'         => '',
			'recommended'     => '',
			'specific_detail' => '',
		);

		// Gather data from WordPress APIs, database, filesystem, etc.
		// Example: Check theme support, active plugins, options, etc.

		return $data;
	}

	/**
	 * Analyze gathered data to determine if issue exists.
	 *
	 * Implements the core detection logic based on gathered data.
	 *
	 * @since  1.YDDD.HHMM
	 * @param  array $data Data gathered from gather_diagnostic_data().
	 * @return bool True if issue detected, false otherwise.
	 */
	private static function analyze_data( $data ) {
		// Implement detection logic
		// Return true if issue found, false if all OK

		if ( $data['count'] > 0 ) {
			return true; // Issue detected
		}

		return false; // All OK
	}
}
