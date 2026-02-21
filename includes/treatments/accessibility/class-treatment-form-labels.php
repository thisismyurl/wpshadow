<?php
/**
 * Form Label Association Treatment
 *
 * Checks that form inputs have associated labels for screen reader users
 * to understand what each field is for.
 *
 * @package    WPShadow
 * @subpackage Treatments\Accessibility
 * @since      1.6035.1700
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Label Association Treatment Class
 *
 * Verifies form inputs have proper label associations.
 * WCAG 2.1 Level A Success Criterion 1.3.1 (Info and Relationships).
 *
 * @since 1.6035.1700
 */
class Treatment_Form_Labels extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'form_labels';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Form Label Association';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies form inputs have associated labels';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1700
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Form_Labels' );
	}
}
