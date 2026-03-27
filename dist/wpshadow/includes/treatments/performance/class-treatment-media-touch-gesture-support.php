<?php
/**
 * Media Touch Gesture Support Treatment
 *
 * Tests touch gesture support for the media picker and
 * validates required scripts for mobile interactions.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Touch_Gesture_Support Class
 *
 * Checks for touch-related scripts and media view settings.
 *
 * @since 1.6093.1200
 */
class Treatment_Media_Touch_Gesture_Support extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-touch-gesture-support';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Touch Gesture Support';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests touch gesture handling in the media picker';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Touch_Gesture_Support' );
	}
}
