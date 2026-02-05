<?php
/**
 * SSL Certificate Health Treatment
 *
 * Verifies HTTPS support and basic certificate health.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1445
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_SSL_Certificate_Health Class
 *
 * Checks that HTTPS is supported and enabled for the site.
 *
 * @since 1.6035.1445
 */
class Treatment_SSL_Certificate_Health extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-health';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Health';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies HTTPS support and SSL configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! wp_is_https_supported() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'HTTPS is not supported or not properly configured. Install a valid SSL certificate.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate-health',
			);
		}

		if ( 0 !== strpos( home_url(), 'https://' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site URL is not using HTTPS. Update WordPress and site URL settings.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate-health',
			);
		}

		return null;
	}
}