<?php
/**
 * SSL Certificate Valid Treatment
 *
 * Checks if SSL certificate is valid and current.
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
 * SSL Certificate Valid Treatment Class
 *
 * Verifies that the SSL certificate is valid, current, and properly
 * configured on the payment page.
 *
 * @since 0.6093.1200
 */
class Treatment_SSL_Certificate_Valid extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-valid';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Valid';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SSL certificate is valid and current';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the SSL certificate valid treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if SSL issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_SSL_Certificate_Valid' );
	}
}
