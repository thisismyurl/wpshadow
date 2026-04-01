<?php
/**
 * REST API Security Headers Treatment
 *
 * Issue #4947: REST API Missing Rate Limiting
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if REST API has rate limiting.
 * Unlimited API access enables brute force and DoS.
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
 * Treatment_REST_API_Security_Headers Class
 *
 * @since 0.6093.1200
 */
class Treatment_REST_API_Security_Headers extends Treatment_Base {

	protected static $slug = 'rest-api-security-headers';
	protected static $title = 'REST API Missing Rate Limiting';
	protected static $description = 'Checks if REST API has security headers and rate limiting';
	protected static $family = 'security';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_REST_API_Security_Headers' );
	}
}
