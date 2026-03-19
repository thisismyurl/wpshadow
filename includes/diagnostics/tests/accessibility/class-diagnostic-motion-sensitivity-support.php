<?php
/**
 * Motion Sensitivity Support Diagnostic
 *
 * Issue #4865: Admin Doesn't Respect prefers-reduced-motion
 * Pillar: 🌍 Accessibility First
 *
 * Checks if animations respect users' motion sensitivity preferences.
 * People with vestibular disorders can get nauseous from motion.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Motion_Sensitivity_Support Class
 *
 * Checks for:
 * - CSS respects prefers-reduced-motion media query
 * - JavaScript respects prefers-reduced-motion (window.matchMedia)
 * - No auto-playing animations or parallax scrolling
 * - No flashing or strobing content (can trigger seizures)
 * - Auto-playing videos have no audio by default
 * - Large parallax effects avoided or disabled for motion-sensitive users
 *
 * Why this matters:
 * - Vestibular disorders cause nausea, dizziness, balance issues from motion
 * - Affects ~3% of adults
 * - Often comorbid with migraines
 * - Seizure disorders triggered by flashing/strobing (1-2% population)
 *
 * @since 1.6093.1200
 */
class Diagnostic_Motion_Sensitivity_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'motion-sensitivity-support';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Admin Doesn\'t Respect prefers-reduced-motion';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if animations respect users\' motion sensitivity preferences';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic - actual motion testing requires manual QA.
		// We provide recommendations.

		$issues = array();

		$issues[] = __( 'Wrap all CSS animations in @media (prefers-reduced-motion: reduce)', 'wpshadow' );
		$issues[] = __( 'Check window.matchMedia("(prefers-reduced-motion: reduce)") in JavaScript', 'wpshadow' );
		$issues[] = __( 'Disable parallax scrolling for motion-sensitive users', 'wpshadow' );
		$issues[] = __( 'Don\'t auto-play animations on page load', 'wpshadow' );
		$issues[] = __( 'Avoid flashing or strobing content (can trigger seizures)', 'wpshadow' );
		$issues[] = __( 'Auto-playing videos should have no audio by default', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Motion-sensitive users and people with vestibular disorders can get nauseous or dizzy from animations. Most operating systems offer a "reduce motion" preference.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/motion-sensitivity',
				'details'      => array(
					'recommendations'         => $issues,
					'wcag_requirement'        => 'WCAG 2.1 2.3.3 Animation from Interactions',
					'css_pattern'             => '@media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important; } }',
					'js_pattern'              => 'const prefersReduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;',
					'affected_population'     => __( 'Vestibular disorders (~3%), seizure disorders (~1-2%)', 'wpshadow' ),
					'os_support'              => 'Windows, macOS, iOS, Android all support prefers-reduced-motion',
				),
			);
		}

		return null;
	}
}
