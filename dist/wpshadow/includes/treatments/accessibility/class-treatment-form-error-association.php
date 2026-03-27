<?php
/**
 * Form Error Association Treatment
 *
 * Checks if error messages use aria-describedby to link to form fields.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Error Association Treatment Class
 *
 * Validates that form errors are programmatically linked to fields.
 *
 * @since 1.6093.1200
 */
class Treatment_Form_Error_Association extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-error-association';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Form Error Messages Not Associated with Fields';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if error messages use aria-describedby to link to fields';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Form_Error_Association' );
	}
}
