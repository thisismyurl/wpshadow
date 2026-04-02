<?php
/**
 * SSL Protocol Version Treatment
 *
 * Checks SSL/TLS protocol version for security.
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
 * SSL Protocol Version Treatment Class
 *
 * Verifies that site is using secure TLS protocols.
 *
 * @since 1.6093.1200
 */
class Treatment_SSL_Protocol_Version extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-protocol-version';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Protocol Version';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks SSL/TLS protocol version for security';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ssl-security';

	/**
	 * Run the SSL protocol version treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if protocol issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_SSL_Protocol_Version' );
	}
}
