<?php
/**
 * No Keyboard Navigation Testing Diagnostic
 *
 * Detects when keyboard navigation has not been tested,
 * potentially locking out keyboard-only users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Keyboard Navigation Testing
 *
 * Checks whether keyboard navigation has been
 * tested for accessibility.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Keyboard_Navigation_Testing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-keyboard-navigation-testing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Keyboard Navigation Testing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether keyboard navigation is tested';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if keyboard navigation testing has been documented
		$has_keyboard_testing = get_option( 'wpshadow_keyboard_navigation_tested' );

		if ( ! $has_keyboard_testing ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Keyboard navigation hasn\'t been tested, which means some users can\'t access your site. Keyboard users: people with motor disabilities, power users preferring efficiency, temporary injuries. Test yourself: Tab through every page, check if you can reach all elements, verify focus is always visible. Common issues: skip links missing, buttons not keyboard-accessible, focus trap in modals, no way to close dropdowns. This is critical WCAG requirement affecting 16% of users.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Accessibility Compliance & User Inclusivity',
					'potential_gain' => 'Enable 16% of users with motor disabilities to navigate',
					'roi_explanation' => 'Keyboard navigation is WCAG AA requirement. Testing ensures 16% of users can access your site.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/keyboard-navigation-testing?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
