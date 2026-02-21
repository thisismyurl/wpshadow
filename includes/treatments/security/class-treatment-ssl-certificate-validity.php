<?php
/**
 * SSL Certificate Validity Treatment
 *
 * Issue #4932: SSL Certificate Expired or Invalid
 * Pillar: 🛡️ Safe by Default / ⚙️ Murphy's Law
 *
 * Checks SSL certificate validity and expiration.
 * Expired certificates cause browser warnings and lost traffic.
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
 * Treatment_SSL_Certificate_Validity Class
 *
 * @since 1.6050.0000
 */
class Treatment_SSL_Certificate_Validity extends Treatment_Base {

	protected static $slug = 'ssl-certificate-validity';
	protected static $title = 'SSL Certificate Expired or Invalid';
	protected static $description = 'Checks SSL certificate status and expiration date';
	protected static $family = 'security';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_SSL_Certificate_Validity' );
	}
}
