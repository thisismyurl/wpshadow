<?php
/**
 * Error Logging Configuration Treatment
 *
 * Issue #4901: Error Logging Not Configured or Publicly Accessible
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if error logging is properly configured.
 * Logs should capture errors but not be publicly accessible.
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
 * Treatment_Error_Logging_Configuration Class
 *
 * @since 1.6050.0000
 */
class Treatment_Error_Logging_Configuration extends Treatment_Base {

	protected static $slug = 'error-logging-configuration';
	protected static $title = 'Error Logging Not Configured or Publicly Accessible';
	protected static $description = 'Checks if errors are logged server-side and not exposed publicly';
	protected static $family = 'security';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Error_Logging_Configuration' );
	}
}
