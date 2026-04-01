<?php
/**
 * Mobile Content Hierarchy Treatment
 *
 * Tests if headings follow a clear hierarchy on mobile.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Content Hierarchy Treatment Class
 *
 * Checks for heading order issues on the homepage.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Content_Hierarchy extends Treatment_Base {

	protected static $slug = 'mobile-content-hierarchy';
	protected static $title = 'Mobile Content Hierarchy';
	protected static $description = 'Tests if headings follow a clear hierarchy on mobile';
	protected static $family = 'design';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Content_Hierarchy' );
	}
}
