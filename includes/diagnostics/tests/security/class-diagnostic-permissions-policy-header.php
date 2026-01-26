<?php
/**
 * Diagnostic: Permissions-Policy Header
 *
 * Checks if the Permissions-Policy (or legacy Feature-Policy) header is present.
 * This header limits powerful browser features and reduces attack surface.
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
 * Class Diagnostic_Permissions_Policy_Header
 *
 * Tests for Permissions-Policy header.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Permissions_Policy_Header extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'permissions-policy-header';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Permissions-Policy Header';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the Permissions-Policy header is present';

	/**
	 * Check Permissions-Policy header.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$response = wp_remote_head( home_url( '/' ) );

		if ( is_wp_error( $response ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Could not check Permissions-Policy header because the site request failed.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/permissions_policy_header',
				'meta'        => array(
					'error_message' => $response->get_error_message(),
				),
			);
		}

		$headers = wp_remote_retrieve_headers( $response );
		$policy  = $headers['permissions-policy'] ?? ( $headers['permission-policy'] ?? null );
		$legacy  = $headers['feature-policy'] ?? null;

		if ( empty( $policy ) && empty( $legacy ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Permissions-Policy header is missing. Add it to restrict powerful browser features (e.g., camera, geolocation, microphone) to trusted origins.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/permissions_policy_header',
				'meta'        => array(
					'permissions_policy' => '',
					'feature_policy'     => '',
				),
			);
		}

		return null;
	}
}
