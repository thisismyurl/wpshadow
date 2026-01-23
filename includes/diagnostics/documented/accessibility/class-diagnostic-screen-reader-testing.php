<?php

/**
 * Diagnostic: Screen Reader Testing Recommendation
 *
 * Reminds admins to test their site with actual screen readers.
 * Automated tests can't catch everything - real testing is essential.
 *
 * Philosophy: Commandment #8 (Inspire Confidence - Accessibility)
 *
 * @package WPShadow
 * @subpackage Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Screen Reader Testing Diagnostic
 *
 * This is an educational diagnostic that encourages manual testing.
 * No automated test can replace actual screen reader usage.
 */
class Diagnostic_Screen_Reader_Testing extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Null if no issues, array with details if issues found
	 */
	public static function run(): ?array
	{
		// Check if user has dismissed this recommendation
		$dismissed = get_option('wpshadow_screen_reader_testing_dismissed', false);

		if ($dismissed) {
			return null;
		}

		return array(
			'title'       => __('Recommendation: Test with Screen Readers', 'wpshadow'),
			'description' => __('Automated accessibility tests can only catch about 30% of issues. Real screen reader testing is essential to ensure your site is truly accessible.', 'wpshadow'),
			'severity'    => 'low',
			'category'    => 'accessibility',
			'impact'      => __('Without testing, you may have accessibility barriers you\'re not aware of. ~15% of WordPress users have disabilities.', 'wpshadow'),
			'details'     => array(
				'free_screen_readers' => array(
					'NVDA (Windows)' => 'https://www.nvaccess.org/',
					'VoiceOver (Mac/iOS)' => 'Built into macOS and iOS (Cmd+F5)',
					'TalkBack (Android)' => 'Built into Android',
					'JAWS (Windows)' => 'Commercial, but has free trial',
				),
				'what_to_test'        => array(
					'Navigate using Tab key only (no mouse)',
					'Turn on screen reader and navigate by headings',
					'Fill out forms using only keyboard + screen reader',
					'Check image descriptions are meaningful',
					'Verify all buttons/links have clear purposes',
				),
				'time_needed'         => '15-30 minutes per key page',
			),
			'kb_link'     => 'https://wpshadow.com/kb/screen-reader-testing',
			'training'    => 'https://wpshadow.com/training/accessibility-testing',
		);
	}

	/**
	 * Get diagnostic metadata
	 *
	 * @return array Metadata about this diagnostic
	 */
	public static function get_meta(): array
	{
		return array(
			'id'          => 'screen_reader_testing',
			'title'       => __('Screen Reader Testing', 'wpshadow'),
			'description' => __('Recommendation to test site with actual screen readers', 'wpshadow'),
			'category'    => 'accessibility',
			'severity'    => 'low',
		);
	}
}
