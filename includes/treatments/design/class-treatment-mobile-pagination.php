<?php
/**
 * Mobile Pagination UI
 *
 * Validates pagination controls for mobile touch interaction.
 *
 * @package    WPShadow
 * @subpackage Treatments\Navigation
 * @since      1.602.1450
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Pagination UI Treatment.
 *
 * @since 1.602.1450
 */
class Treatment_Mobile_Pagination extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-pagination-ui';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Pagination UI';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates pagination for mobile touch';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'navigation';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1450
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Pagination' );
	}
}
