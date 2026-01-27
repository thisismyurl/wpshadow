<?php
/**
 * Diagnostic: REST Controlled Route Access
 *
 * Checks if sensitive REST API routes (e.g., wp/v2/users) are properly protected.
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
 * Class Diagnostic_Rest_Controlled_Route
 *
 * Tests protection of sensitive REST routes.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Rest_Controlled_Route extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'rest-controlled-route';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'REST Controlled Route Access';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if sensitive REST routes are protected';

	/**
	 * Check sensitive REST route protection.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$users_route = rest_url( 'wp/v2/users' );
		$response   = wp_remote_get( $users_route );

		if ( is_wp_error( $response ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Could not verify REST users route protection because the request failed.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/rest_controlled_route',
				'meta'        => array(
					'endpoint'      => $users_route,
					'error_message' => $response->get_error_message(),
				),
			);
		}

		$status = wp_remote_retrieve_response_code( $response );

		if ( 200 === $status ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'REST users endpoint is publicly accessible (HTTP 200). This can expose user data. Require authentication for wp/v2/users.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/rest_controlled_route',
				'meta'        => array(
					'endpoint'    => $users_route,
					'http_status' => $status,
				),
			);
		}

		return null;
	}
}
