<?php
/**
 * Mobile Reduce Motion Preference Treatment
 *
 * Checks for prefers-reduced-motion media query support to respect user's motion sensitivity preferences.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Reduce Motion Preference Treatment Class
 *
 * Validates that animations and motion effects respect the prefers-reduced-motion media query
 * preference, ensuring accessibility for vestibular disorder users and WCAG AAA compliance.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Reduce_Motion_Preference extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-reduce-motion-preference';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Reduce Motion Preference';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Check for prefers-reduced-motion media query support to respect user motion sensitivity preferences';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Reduce_Motion_Preference' );
	}
}
