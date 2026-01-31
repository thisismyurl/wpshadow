<?php
/**
 * Expired SSL Certificate Check Not Scheduled Diagnostic
 *
 * Checks if SSL expiration check is scheduled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Expired SSL Certificate Check Not Scheduled Diagnostic Class
 *
 * Detects missing SSL expiration monitoring.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Expired_SSL_Certificate_Check_Not_Scheduled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'expired-ssl-certificate-check-not-scheduled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Expired SSL Certificate Check Not Scheduled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SSL expiration check is scheduled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if SSL expiration check is scheduled
		if ( ! wp_next_scheduled( 'check_ssl_certificate_expiration' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'SSL certificate expiration monitoring is not scheduled. Schedule regular checks to receive notifications before your certificate expires.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/expired-ssl-certificate-check-not-scheduled',
			);
		}

		return null;
	}
}
