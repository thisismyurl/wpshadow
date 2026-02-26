<?php
/**
 * SSL Certificate Domain Validity Treatment
 *
 * Validates that the SSL certificate covers the site domain.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.0900
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL Certificate Domain Validity Treatment Class
 *
 * Detects domain mismatches between certificate SAN/CN and site domain.
 *
 * @since 1.6035.0900
 */
class Treatment_SSL_Certificate_Domain_Validity extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-domain-validity';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Domain Validity';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the SSL certificate matches the site domain and SANs';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.0900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_SSL_Certificate_Domain_Validity' );
	}
}
