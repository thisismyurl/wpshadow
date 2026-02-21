<?php
/**
 * Form Error Prevention Treatment
 *
 * Issue #4961: No Error Prevention for Legal/Financial Forms
 * Pillar: 🛡️ Safe by Default / #8: Inspire Confidence
 *
 * Checks if important forms have error prevention.
 * Legal and financial submissions need review before submit.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Form_Error_Prevention Class
 *
 * @since 1.6050.0000
 */
class Treatment_Form_Error_Prevention extends Treatment_Base {

	protected static $slug = 'form-error-prevention';
	protected static $title = 'No Error Prevention for Legal/Financial Forms';
	protected static $description = 'Checks if critical forms allow review before submission';
	protected static $family = 'reliability';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Form_Error_Prevention' );
	}
}
