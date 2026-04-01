<?php
/**
 * Form Validation Timing Treatment
 *
 * Issue #4944: Form Validation Only on Submit (No Inline Help)
 * Pillar: 🎓 Learning Inclusive / #1: Helpful Neighbor
 *
 * Checks if forms validate inline.
 * Waiting until submit to show errors wastes user time.
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
 * Treatment_Form_Validation_Timing Class
 *
 * @since 0.6093.1200
 */
class Treatment_Form_Validation_Timing extends Treatment_Base {

	protected static $slug = 'form-validation-timing';
	protected static $title = 'Form Validation Only on Submit (No Inline Help)';
	protected static $description = 'Checks if forms validate fields as users type';
	protected static $family = 'content';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Form_Validation_Timing' );
	}
}
