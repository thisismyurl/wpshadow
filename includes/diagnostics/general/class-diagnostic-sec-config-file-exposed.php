<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Publicly Accessible Config Files
 *
 * Tests if wp-config.php, .env, .git accessible via URL. Instant site takeover risk.
 *
 * Philosophy: Commandment #1, 8 - Helpful Neighbor - Anticipate needs, Inspire Confidence - Intuitive UX
 * Priority: 1 (1=Must-Have, 2=Should-Have, 3=Nice-to-Have)
 * Threat Level: 100/100
 *
 * Impact: Prevents \"Your database password is publicly viewable\" disasters.
 */
class Diagnostic_SecConfigFileExposed extends Diagnostic_Base {
	protected static $slug = 'sec-config-file-exposed';

	protected static $title = 'Sec Config File Exposed';

	protected static $description = 'Automatically initialized lean diagnostic for Sec Config File Exposed. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';


	/**
	 * Get diagnostic ID
	 *
	 * @return string
	 */
	public static function get_id(): string {
		return 'sec-config-file-exposed';
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'Publicly Accessible Config Files', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Tests if wp-config.php, .env, .git accessible via URL. Instant site takeover risk.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'security';
	}

	/**
	 * Get threat level (0-100)
	 * Higher = more critical
	 *
	 * @return int
	 */
	public static function get_threat_level(): int {
		return 100;
	}

	/**
	 * Run the diagnostic
	 *
	 * @return array Result with status, message, and data
	 */
	public static function run(): array {
		// STUB: Implement sec-config-file-exposed diagnostic
		//
		// This is a KILLER test that delivers "Holy Sh*t" moments:
		// Prevents \"Your database password is publicly viewable\" disasters.
		//
		// Implementation notes:
		// - Quantify impact with real numbers (dollar amounts, percentages)
		// - Show specific examples (file names, URLs, exact problems)
		// - Provide actionable fix recommendations
		// - Link to KB article explaining why this matters
		// - Track KPI: time saved, revenue impact, disaster prevented

		return array(
			'status'  => 'todo',
			'message' => __( 'Not yet implemented - Priority 1 killer test', 'wpshadow' ),
			'data'    => array(
				'impact'   => 'Prevents \"Your database password is publicly viewable\" disasters.',
				'priority' => 1,
			),
		);
	}

	/**
	 * Get KB article URL
	 *
	 * @return string
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/config-file-exposed';
	}

	/**
	 * Get training video URL
	 *
	 * @return string
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/config-file-exposed';
	}

	public static function check(): ?array {
		if ( ! ( false ) ) {
			return null;
		}

		return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
			'sec-config-file-exposed',
			'Sec Config File Exposed',
			'Automatically initialized lean diagnostic for Sec Config File Exposed. Optimized for minimal overhead while surfacing high-value signals.',
			'general',
			'low',
			30,
			'sec-config-file-exposed'
		);
	}
}
