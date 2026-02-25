<?php
/**
 * Mobile Reduce Motion Preference
 *
 * Respects prefers-reduced-motion media query and disables animations.
 *
 * @package    WPShadow
 * @subpackage Treatments\Accessibility
 * @since      1.602.1430
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Reduce Motion Preference
 *
 * Validates that animations and transitions respect the
 * prefers-reduced-motion media query for accessibility.
 * WCAG 2.3.3 Level AAA requirement.
 *
 * @since 1.602.1430
 */
class Treatment_Mobile_Reduce_Motion extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-reduce-motion';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Reduce Motion Preference';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates animations respect prefers-reduced-motion';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Reduce_Motion' );
	}
}
