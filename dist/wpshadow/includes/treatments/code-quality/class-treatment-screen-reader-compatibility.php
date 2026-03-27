<?php
/**
 * Screen Reader Compatibility Treatment
 *
 * Tests if site is compatible with screen readers for blind users.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Screen Reader Compatibility Treatment Class
 *
 * Validates that the site works properly with screen readers
 * including proper semantic HTML and ARIA labels.
 *
 * @since 1.6093.1200
 */
class Treatment_Screen_Reader_Compatibility extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'screen-reader-compatibility';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Screen Reader Compatibility';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site is compatible with screen readers for blind users';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * Tests screen reader compatibility including alt text, ARIA labels,
	 * semantic HTML, and heading hierarchy.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Screen_Reader_Compatibility' );
	}
}
