<?php
/**
 * HTTPS Redirect Working Treatment
 *
 * Verifies HTTP requests redirect to HTTPS.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1420
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_HTTPS_Redirect_Working Class
 *
 * Checks that HTTP requests redirect to HTTPS when HTTPS is supported.
 *
 * @since 1.6035.1420
 */
class Treatment_HTTPS_Redirect_Working extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'https-redirect-working';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'HTTPS Redirect Working';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks that HTTP redirects to HTTPS';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1420
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_HTTPS_Redirect_Working' );
	}
}