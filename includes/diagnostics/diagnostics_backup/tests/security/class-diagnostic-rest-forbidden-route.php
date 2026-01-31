<?php
/**
 * Diagnostic: REST Forbidden Route Check
 *
 * Ensures sensitive REST routes are not publicly accessible.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Rest_Forbidden_Route
 *
 * Tests that sensitive REST endpoints require authentication.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Rest_Forbidden_Route extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'rest-forbidden-route';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'REST Forbidden Route Check';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks that sensitive REST routes are not publicly accessible';

	/**
	 * Check sensitive REST route accessibility.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$route    = rest_url( 'wp/v2/users' );
		$response = wp_remote_get( $route );

		if ( is_wp_error( $response ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Could not verify REST route protection because the request failed.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/rest_forbidden_route',
				'meta'        => array(
					'endpoint'      => $route,
					'error_message' => $response->get_error_message(),
				),
			);
		}

		$status = wp_remote_retrieve_response_code( $response );

		if ( 200 === $status ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'REST users endpoint returned HTTP 200 without authentication. Sensitive user data may be exposed; require authentication for wp/v2/users.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/rest_forbidden_route',
				'meta'        => array(
					'endpoint'    => $route,
					'http_status' => $status,
				),
			);
		}

		return null;
	}
}
