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
		if ( ! wp_is_https_supported() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'HTTPS is not supported on this site. Install and configure SSL.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/https-redirect-working',
			);
		}

		$http_url = preg_replace( '/^https:/i', 'http:', home_url( '/' ) );
		$response = wp_remote_head(
			$http_url,
			array(
				'timeout'     => 5,
				'redirection' => 0,
				'sslverify'   => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$status  = (int) wp_remote_retrieve_response_code( $response );
		$headers = wp_remote_retrieve_headers( $response );
		$location = is_array( $headers ) && isset( $headers['location'] ) ? $headers['location'] : '';

		if ( $status >= 300 && $status < 400 && is_string( $location ) && 0 === strpos( $location, 'https://' ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'HTTP requests are not redirecting to HTTPS. Enable a site-wide HTTPS redirect.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/https-redirect-working',
			'meta'         => array(
				'http_status' => $status,
				'location'    => $location,
			),
		);
	}
}