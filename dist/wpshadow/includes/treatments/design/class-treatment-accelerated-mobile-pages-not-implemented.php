<?php
/**
 * Accelerated Mobile Pages Not Implemented Treatment
 *
 * Checks AMP implementation.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Accelerated_Mobile_Pages_Not_Implemented Class
 *
 * Performs treatment check for Accelerated Mobile Pages Not Implemented.
 *
 * @since 0.6093.1200
 */
class Treatment_Accelerated_Mobile_Pages_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'accelerated-mobile-pages-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Accelerated Mobile Pages Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks AMP implementation';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Accelerated_Mobile_Pages_Not_Implemented' );
	}
}
