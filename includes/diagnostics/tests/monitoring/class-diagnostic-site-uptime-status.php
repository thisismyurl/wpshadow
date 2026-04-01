<?php
/**
 * Site Uptime Status Diagnostic
 *
 * Checks whether the site is responding to HTTP requests.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Site_Uptime_Status Class
 *
 * Performs a lightweight HTTP HEAD request against the site homepage
 * to confirm the site is responding.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Site_Uptime_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-uptime-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Uptime Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the site is responding to HTTP requests';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cached = get_transient( 'wpshadow_site_uptime_status' );
		if ( is_array( $cached ) && isset( $cached['status'] ) ) {
			return $cached['status'] ? null : $cached['finding'];
		}

		$response = wp_remote_head(
			home_url( '/' ),
			array(
				'timeout'   => 5,
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: error message */
					__( 'Site check failed: %s', 'wpshadow' ),
					$response->get_error_message()
				),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/site-uptime-status?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'error_code' => $response->get_error_code(),
				),
			);

			set_transient(
				'wpshadow_site_uptime_status',
				array(
					'status'  => false,
					'finding' => $finding,
				),
				MINUTE_IN_SECONDS * 5
			);

			return $finding;
		}

		$status_code = (int) wp_remote_retrieve_response_code( $response );

		if ( $status_code >= 500 || 0 === $status_code ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: HTTP status code */
					__( 'Site is returning HTTP %d. This indicates downtime or server errors.', 'wpshadow' ),
					$status_code
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/site-uptime-status?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'http_status' => $status_code,
				),
			);

			set_transient(
				'wpshadow_site_uptime_status',
				array(
					'status'  => false,
					'finding' => $finding,
				),
				MINUTE_IN_SECONDS * 5
			);

			return $finding;
		}

		if ( $status_code >= 400 ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: HTTP status code */
					__( 'Site is returning HTTP %d. Check routing, redirects, or access rules.', 'wpshadow' ),
					$status_code
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/site-uptime-status?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'http_status' => $status_code,
				),
			);

			set_transient(
				'wpshadow_site_uptime_status',
				array(
					'status'  => false,
					'finding' => $finding,
				),
				MINUTE_IN_SECONDS * 5
			);

			return $finding;
		}

		set_transient(
			'wpshadow_site_uptime_status',
			array(
				'status' => true,
			),
			MINUTE_IN_SECONDS * 5
		);

		return null;
	}
}
