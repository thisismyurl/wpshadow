<?php
/**
 * HTTP to HTTPS Redirect Treatment
 *
 * Issue #4931: No HTTP to HTTPS Redirect Configured
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if HTTP traffic redirects to HTTPS.
 * Mixed content warnings and security issues without HTTPS enforcement.
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
 * Treatment_HTTP_HTTPS_Redirect Class
 *
 * @since 1.6093.1200
 */
class Treatment_HTTP_HTTPS_Redirect extends Treatment_Base {

	protected static $slug = 'http-https-redirect';
	protected static $title = 'No HTTP to HTTPS Redirect Configured';
	protected static $description = 'Checks if HTTP traffic is redirected to HTTPS';
	protected static $family = 'security';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_HTTP_HTTPS_Redirect' );
	}
}
