<?php
/**
 * Diagnostic: wp_remote_head Function
 *
 * Checks if WordPress HTTP requests are working (wp_remote_head test).
 *
 * @package    WPShadow
 * @subpackage Diagnostics\API
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Wp_Remote_Head
 *
 * Tests WordPress HTTP request functionality via wp_remote_head.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Wp_Remote_Head extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wp-remote-head';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'wp_remote_head Function';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests if WordPress HTTP requests are working';

	/**
	 * Check wp_remote_head functionality.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Test wp_remote_head on home URL.
		$url      = home_url( '/' );
		$response = wp_remote_head(
			$url,
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
					__( 'wp_remote_head() test failed: %s. WordPress HTTP requests may not be working. Check server firewall and PHP stream context.', 'wpshadow' ),
					$response->get_error_message()
				),
				'severity'    => 'medium',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp_remote_head',
				'meta'        => array(
					'url'             => $url,
					'error_message'   => $response->get_error_message(),
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
					__( 'wp_remote_head() returned HTTP %d. HTTP requests may be misconfigured.', 'wpshadow' ),
					$status
				),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp_remote_head',
				'meta'        => array(
					'url'         => $url,
					'http_status' => $status,
				),
			);
		}

		return null;
	}
}
