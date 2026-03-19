<?php
/**
 * Form Fields Missing Autocomplete Treatment
 *
 * Checks if form fields have autocomplete attributes.
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
 * Form Autocomplete Treatment Class
 *
 * Validates that form fields use autocomplete attributes for common data types.
 *
 * @since 1.6093.1200
 */
class Treatment_Form_Fields_Missing_Autocomplete extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-fields-missing-autocomplete';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Form Fields Missing Autocomplete Attributes';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if form fields have autocomplete attributes';

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
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Form_Fields_Missing_Autocomplete' );
	}
}
