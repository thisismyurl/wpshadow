<?php
/**
 * wp_remote_post Function Diagnostic
 *
 * Tests if wp_remote_post() works correctly for external API calls.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Remote Post Diagnostic Class
 *
 * Tests external POST request functionality.
 */
class Diagnostic_WP_Remote_Post extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wp-remote-post';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'wp_remote_post Function Test';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies wp_remote_post() works for external API calls';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'api';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Test POST to a reliable endpoint.
		$test_url = 'https://httpbin.org/post';
		$response = wp_remote_post(
			$test_url,
			array(
				'timeout' => 5,
				'body'    => array( 'test' => 'wpshadow' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'wp_remote_post() function is failing', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp_remote_post',
				'evidence'     => array(
					'error_message' => $response->get_error_message(),
					'error_code'    => $response->get_error_code(),
					'test_url'      => $test_url,
					'recommendation' => __( 'Check server firewall rules, PHP curl extension, or external request blocking', 'wpshadow' ),
				),
			);
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		
		if ( 200 !== $response_code ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: HTTP response code */
					__( 'wp_remote_post() returned unexpected status code: %d', 'wpshadow' ),
					$response_code
				),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp_remote_post',
				'evidence'     => array(
					'response_code' => $response_code,
					'test_url'      => $test_url,
					'recommendation' => __( 'External POST requests may be partially blocked', 'wpshadow' ),
				),
			);
		}

		// Function works correctly.
		return null;
	}
}
