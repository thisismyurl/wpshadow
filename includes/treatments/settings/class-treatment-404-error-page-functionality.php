<?php
/**
 * 404 Error Page Functionality Treatment
 *
 * Validates that the 404 error page is properly configured with appropriate
 * template, search functionality, and helpful content for users.
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
 * 404 Error Page Functionality Treatment Class
 *
 * Checks 404 template implementation and configuration.
 *
 * @since 1.6093.1200
 */
class Treatment_404_Error_Page_Functionality extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = '404-error-page-functionality';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = '404 Error Page Functionality';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates 404 error page configuration';

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
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_404_Error_Page_Functionality' );
	}
}
