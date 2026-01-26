<?php
/**
 * Retention Beta Program Diagnostic
 *
 * Checks if users have opted into the WPShadow beta program to test
 * new features early and provide valuable feedback. Beta participation
 * improves customer retention by increasing engagement and making users
 * feel valued as part of the product development process.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_RetentionBetaProgram Class
 *
 * Detects when users have not joined the beta testing program.
 * Beta testers get early access to features, influence the product roadmap,
 * and earn gamification points for their contributions.
 *
 * Philosophy Alignment:
 * - Commandment #4: Advice, Not Sales - Suggests joining beta as helpful opportunity
 * - Commandment #6: Drive to Free Training - Links to beta program information
 * - Commandment #9: Everything Has a KPI - Tracks beta program enrollment
 *
 * @since 1.2601.2148
 */
class Diagnostic_RetentionBetaProgram extends Diagnostic_Base {

	/**
	 * The diagnostic slug/ID
	 *
	 * @var string
	 */
	protected static $slug = 'retention-beta-program';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Retention Beta Program';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if user has opted into the WPShadow beta testing program';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'general';

	/**
	 * Display name for the family
	 *
	 * @var string
	 */
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if the user has enabled the beta program option. If not enabled,
	 * returns a finding suggesting they join to get early access to features
	 * and help shape the product.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if user hasn't joined beta, null otherwise.
	 */
	public static function check(): ?array {
		// Check if beta program is enabled.
		$beta_enabled = get_option( 'wpshadow_beta_program_enabled', false );

		// If beta program is not enabled, suggest joining.
		if ( ! $beta_enabled ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'retention-beta-program',
				__( 'Join the Beta Program', 'wpshadow' ),
				__( 'You\'re not enrolled in the WPShadow beta program. Join to get early access to new features, earn 100 gamification points, and help shape the product roadmap with your feedback. Beta testers also earn points for bug reports (50 pts) and feature requests (25 pts).', 'wpshadow' ),
				'general',
				'low',
				25,
				'retention-beta-program'
			);
		}

		// Beta program is enabled, no issue.
		return null;
	}

	/**
	 * Get diagnostic ID
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'retention-beta-program';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since  1.2601.2148
	 * @return string Human-readable diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Are users joining the beta program?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Are users joining the beta program?. Part of Customer Retention analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since  1.2601.2148
	 * @return string Category identifier.
	 */
	public static function get_category(): string {
		return 'customer_retention';
	}

	/**
	 * Run the diagnostic test
	 *
	 * Legacy method for backwards compatibility.
	 * Calls check() internally.
	 *
	 * @since  1.2601.2148
	 * @return array Finding data or empty if no issue.
	 */
	public static function run(): array {
		$result = self::check();
		return $result ?? array();
	}

	/**
	 * Get threat level for this finding (0-100)
	 *
	 * Beta program enrollment is a retention opportunity, not a security threat.
	 * Low threat level indicates it's an optimization suggestion.
	 *
	 * @since  1.2601.2148
	 * @return int Threat level (25 = low priority retention opportunity).
	 */
	public static function get_threat_level(): int {
		return 25;
	}

	/**
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string Knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/retention-beta-program/';
	}

	/**
	 * Get training video URL
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/retention-beta-program/';
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Retention Beta Program
	 * Slug: retention-beta-program
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (user joined beta)
	 * - FAIL: check() returns array when diagnostic condition IS met (user hasn't joined)
	 * - Description: Checks if user has opted into the WPShadow beta testing program
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result information.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_retention_beta_program(): array {
		$result = self::check();

		// Get the current beta program state.
		$beta_enabled = get_option( 'wpshadow_beta_program_enabled', false );

		// Test logic:
		// - If beta is enabled, check() should return null (no issue = passed).
		// - If beta is disabled, check() should return array (issue found = expected).
		if ( $beta_enabled ) {
			$passed  = null === $result;
			$message = $passed
				? __( 'Test passed: Beta program enabled, check() correctly returned null', 'wpshadow' )
				: __( 'Test failed: Beta program enabled but check() returned a finding', 'wpshadow' );
		} else {
			$passed  = is_array( $result );
			$message = $passed
				? __( 'Test passed: Beta program disabled, check() correctly returned finding', 'wpshadow' )
				: __( 'Test failed: Beta program disabled but check() returned null', 'wpshadow' );
		}

		return array(
			'passed'  => $passed,
			'message' => $message,
		);
	}
}
