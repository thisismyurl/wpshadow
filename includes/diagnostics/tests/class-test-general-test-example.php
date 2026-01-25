<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Test the scaffolder
 *
 * Philosophy: [TODO] Which commandments does this serve?
 * Examples:
 *   - Commandment #1 (Helpful Neighbor): Educate users why this matters
 *   - Commandment #8 (Inspire Confidence): Explain what we're checking and why
 *   - Commandment #9 (Show Value): Will track KPIs when users act on findings
 *
 * KB Article: https://wpshadow.com/kb/test_example
 * Training Video: https://wpshadow.com/training/test_example
 *
 * @todo Implement run() method - check Test the scaffolder
 * @todo Add KPI tracking when findings are resolved
 * @todo Link to specific KB section if multi-part article
 */
class Test_General_Testexample extends Diagnostic_Base {

	/**
	 * Diagnostic ID
	 */
	protected static string $id = 'test-general-test-example';

	/**
	 * Category for grouping
	 */
	protected static string $category = 'general';

	/**
	 * Run the diagnostic check
	 *
	 * @return array Found issues (empty = healthy)
	 */
	public static function run(): array {
		// TODO: Implement the check
		// Examples:
		//   - Query WordPress options/settings
		//   - Check server configuration
		//   - Validate security headers
		//   - Analyze plugin/theme data

		// Return format:
		// [
		//     'severity' => 'critical|warning|info',
		//     'message' => 'What we found',
		//     'recommendation' => 'How to fix it',
		//     'learning_link' => 'https://wpshadow.com/kb/...',
		// ]

		return array();
	}

	/**
	 * Get display name (plain English, no jargon)
	 */
	public static function get_name(): string {
		return __( 'Test the scaffolder', 'wpshadow' );
	}

	/**
	 * Get description with KB link (educational, helpful)
	 */
	public static function get_description(): string {
		return sprintf(
			__( 'Test the scaffolder. <a href="%s" target="_blank">Learn why this matters</a>', 'wpshadow' ),
			'https://wpshadow.com/kb/test_example'
		);
	}
}
