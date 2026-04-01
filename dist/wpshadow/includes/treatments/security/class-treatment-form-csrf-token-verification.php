<?php
/**
 * Form CSRF Token Verification Treatment
 *
 * Issue #4987: Forms Not Protected Against CSRF
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if forms use nonces for CSRF protection.
 * Forms without nonces are vulnerable to forging.
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
 * Treatment_Form_CSRF_Token_Verification Class
 *
 * @since 0.6093.1200
 */
class Treatment_Form_CSRF_Token_Verification extends Treatment_Base {

	protected static $slug = 'form-csrf-token-verification';
	protected static $title = 'Forms Not Protected Against CSRF';
	protected static $description = 'Checks if forms use nonces for CSRF protection';
	protected static $family = 'security';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Form_CSRF_Token_Verification' );
	}
}
