<?php
/**
 * Security Headers Configuration Treatment
 *
 * Issue #4902: Missing Security Headers (CSP, HSTS, X-Frame)
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if HTTP security headers are configured.
 * Security headers provide defense-in-depth against common attacks.
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
 * Treatment_Security_Headers_Configuration Class
 *
 * @since 1.6093.1200
 */
class Treatment_Security_Headers_Configuration extends Treatment_Base {

	protected static $slug = 'security-headers-configuration';
	protected static $title = 'Missing Security Headers (CSP, HSTS, X-Frame)';
	protected static $description = 'Checks if HTTP security headers are properly configured';
	protected static $family = 'security';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Security_Headers_Configuration' );
	}
}
