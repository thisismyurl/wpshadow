<?php
/**
 * Error Message Clarity Treatment
 *
 * Issue #4917: Error Messages Not Actionable
 * Pillar: #1: Helpful Neighbor / 🎓 Learning Inclusive
 *
 * Checks if error messages explain what to do.
 * "Error 500" is useless. "Your password must be 12 characters" is helpful.
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
 * Treatment_Error_Message_Clarity Class
 *
 * @since 0.6093.1200
 */
class Treatment_Error_Message_Clarity extends Treatment_Base {

	protected static $slug = 'error-message-clarity';
	protected static $title = 'Error Messages Not Actionable';
	protected static $description = 'Checks if error messages explain how to fix issues';
	protected static $family = 'content';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Error_Message_Clarity' );
	}
}
