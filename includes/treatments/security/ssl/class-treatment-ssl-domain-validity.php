<?php
/**
 * SSL Domain Validity Treatment
 *
 * Checks if SSL certificate matches the domain.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1545
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL Domain Validity Treatment Class
 *
 * Verifies SSL certificate is valid for the current domain.
 * Like checking that your security badge has the right name on it.
 *
 * @since 1.6035.1545
 */
class Treatment_Ssl_Domain_Validity extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-domain-validity';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Domain Validity';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SSL certificate matches the domain';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ssl';

	/**
	 * Run the SSL domain validity treatment check.
	 *
	 * @since  1.6035.1545
	 * @return array|null Finding array if domain validity issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Ssl_Domain_Validity' );
	}
}
