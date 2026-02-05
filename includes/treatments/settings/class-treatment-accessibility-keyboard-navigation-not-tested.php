<?php
/**
 * Accessibility Keyboard Navigation Not Tested Treatment
 *
 * Checks if keyboard navigation is tested.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Keyboard Navigation Not Tested Treatment Class
 *
 * Detects untested keyboard navigation.
 *
 * @since 1.6030.2352
 */
class Treatment_Accessibility_Keyboard_Navigation_Not_Tested extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'accessibility-keyboard-navigation-not-tested';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Accessibility Keyboard Navigation Not Tested';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if keyboard navigation is tested';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if keyboard navigation testing is documented
		if ( ! get_option( 'keyboard_nav_test_date' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Keyboard navigation is not tested. Test all interactive elements to ensure they can be accessed using Tab, Enter, and arrow keys.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/accessibility-keyboard-navigation-not-tested',
			);
		}

		return null;
	}
}
