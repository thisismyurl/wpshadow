<?php
/**
 * Error Message Leakage Prevention Treatment
 *
 * Issue #4988: Error Messages Leak Information
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if error messages expose sensitive info.
 * Database errors leak table names and structure.
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
 * Treatment_Error_Message_Leakage_Prevention Class
 *
 * @since 1.6093.1200
 */
class Treatment_Error_Message_Leakage_Prevention extends Treatment_Base {

	protected static $slug = 'error-message-leakage-prevention';
	protected static $title = 'Error Messages Leak Information';
	protected static $description = 'Checks if error messages expose sensitive information';
	protected static $family = 'security';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Error_Message_Leakage_Prevention' );
	}
}
