<?php
/**
 * Diagnostic: Uptime Ping Test
 *
 * Performs a basic ping test to verify site is responding.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Monitoring
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Uptime_Ping_Test
 *
 * Tests site uptime via HTTP HEAD request.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Uptime_Ping_Test extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'uptime-ping-test';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Uptime Ping Test';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Performs a basic ping test to verify site is responding';

	/**
	 * Check site uptime via ping.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$home_url = home_url( '/' );
		$response = wp_remote_head(
			$home_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Error message */
					__( 'Uptime ping test failed: %s. Site may be down or unreachable.', 'wpshadow' ),
					$response->get_error_message()
				),
				'severity'    => 'high',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/uptime_ping_test',
				'meta'        => array(
					'home_url'      => $home_url,
					'error_message' => $response->get_error_message(),
				),
			);
		}

		$status = wp_remote_retrieve_response_code( $response );

		if ( $status >= 400 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: HTTP status code */
					__( 'Uptime ping returned HTTP %d. Site is not responding normally.', 'wpshadow' ),
					$status
				),
				'severity'    => 'high',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/uptime_ping_test',
				'meta'        => array(
					'home_url'     => $home_url,
					'http_status'  => $status,
				),
			);
		}

		return null;
	}
}
