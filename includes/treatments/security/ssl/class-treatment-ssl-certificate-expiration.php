<?php
/**
 * SSL Certificate Expiration Treatment
 *
 * Checks if SSL certificate is expiring soon.
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
 * SSL Certificate Expiration Treatment Class
 *
 * Monitors SSL certificate validity and expiration.
 * Like checking when your security badge expires.
 *
 * @since 1.6093.1200
 */
class Treatment_Ssl_Certificate_Expiration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-expiration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Expiration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SSL certificate is expiring soon';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ssl';

	/**
	 * Run the SSL certificate expiration treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if certificate expiration issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_SSL_Certificate_Expiration' );
	}
}
