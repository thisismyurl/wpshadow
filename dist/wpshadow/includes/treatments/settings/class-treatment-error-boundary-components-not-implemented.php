<?php
/**
 * Error Boundary Components Not Implemented Treatment
 *
 * Checks if error boundary components are implemented.
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
 * Error Boundary Components Not Implemented Treatment Class
 *
 * Detects missing error boundary implementation.
 *
 * @since 1.6093.1200
 */
class Treatment_Error_Boundary_Components_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'error-boundary-components-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Error Boundary Components Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if error boundary components are implemented';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Error_Boundary_Components_Not_Implemented' );
	}
}
