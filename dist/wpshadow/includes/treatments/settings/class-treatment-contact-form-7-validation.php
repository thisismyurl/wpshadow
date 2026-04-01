<?php
/**
 * Contact Form 7 Form Field Validation and Security Treatment
 *
 * Checks Contact Form 7 forms for proper validation, sanitization, and security measures.
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
 * Contact Form 7 Validation Treatment Class
 *
 * Verifies CF7 forms have proper validation and security measures in place.
 *
 * @since 0.6093.1200
 */
class Treatment_Contact_Form_7_Validation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'contact-form-7-validation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Contact Form 7 Form Field Validation and Security';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures CF7 forms have proper validation, sanitization, and security measures';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Contact_Form_7_Validation' );
	}
}
