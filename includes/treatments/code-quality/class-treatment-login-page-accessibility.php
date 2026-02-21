<?php
/**
 * Login Page Accessibility Treatment
 *
 * Checks WordPress login page for accessibility compliance.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2230
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login Page Accessibility Treatment
 *
 * Validates WCAG compliance of login page elements and interaction patterns.
 *
 * @since 1.6030.2230
 */
class Treatment_Login_Page_Accessibility extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'login-page-accessibility';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Login Page Accessibility';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks WordPress login page for accessibility compliance';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Login_Page_Accessibility' );
	}
}
