<?php
/**
 * Mobile List Formatting Treatment
 *
 * Tests if lists are formatted clearly on mobile.
 *
 * @since   1.6050.0000
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile List Formatting Treatment Class
 *
 * Checks for list markup and block list classes.
 *
 * @since 1.6050.0000
 */
class Treatment_Mobile_List_Formatting extends Treatment_Base {

	protected static $slug = 'mobile-list-formatting';
	protected static $title = 'Mobile List Formatting';
	protected static $description = 'Tests if lists are formatted clearly on mobile';
	protected static $family = 'design';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_List_Formatting' );
	}
}
