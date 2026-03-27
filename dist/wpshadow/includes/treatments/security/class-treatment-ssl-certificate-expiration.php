<?php
/**
 * SSL Certificate Expiration Treatment
 *
 * Checks SSL certificate expiration dates and warns before expiry.
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
 * Retrieves certificate metadata and evaluates days until expiry.
 *
 * @since 1.6093.1200
 */
class Treatment_SSL_Certificate_Expiration extends Treatment_Base {

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
	protected static $description = 'Checks how many days remain before the SSL certificate expires';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_SSL_Certificate_Expiration' );
	}
}
