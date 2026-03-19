<?php
/**
 * NextGen Mobile First Design Not Implemented Treatment
 *
 * Checks if mobile first design is implemented.
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
 * NextGen Mobile First Design Not Implemented Treatment Class
 *
 * Detects missing mobile first approach.
 *
 * @since 1.6093.1200
 */
class Treatment_NextGen_Mobile_First_Design_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'nextgen-mobile-first-design-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'NextGen Mobile First Design Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if mobile first design is implemented';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_NextGen_Mobile_First_Design_Not_Implemented' );
	}
}
