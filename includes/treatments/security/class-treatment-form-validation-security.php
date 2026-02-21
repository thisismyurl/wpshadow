<?php
/**
 * Form Validation Security Treatment
 *
 * Tests if forms have proper client-side and server-side validation security.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.1020
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Validation Security Treatment Class
 *
 * Validates that forms have proper security measures including nonce
 * validation, CSRF protection, and input sanitization.
 *
 * @since 1.7034.1020
 */
class Treatment_Form_Validation_Security extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-validation-security';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Form Validation Security';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if forms have proper validation and security measures';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Tests if forms have nonce protection, CSRF prevention,
	 * input validation, and server-side sanitization.
	 *
	 * @since  1.7034.1020
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Form_Validation_Security' );
	}
}
