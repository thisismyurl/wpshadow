<?php
/**
 * Diagnostic: Referrer-Policy Header
 *
 * Checks if the Referrer-Policy header is present.
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
 * Class Diagnostic_Referrer_Policy_Header
 *
 * Tests presence of Referrer-Policy header.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Referrer_Policy_Header extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'referrer-policy-header';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Referrer-Policy Header';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the Referrer-Policy header is set';

	/**
	 * Check Referrer-Policy header.
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
				'description' => __( 'Could not check Referrer-Policy header because the request failed.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/referrer_policy_header',
				'meta'        => array(
					'error_message' => $response->get_error_message(),
				),
			);
		}

		$headers = wp_remote_retrieve_headers( $response );
		$policy  = $headers['referrer-policy'] ?? null;

		if ( empty( $policy ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Referrer-Policy header is missing. Add it to limit referrer data leakage (recommended: no-referrer-when-downgrade or same-origin).', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/referrer_policy_header',
				'meta'        => array(
					'referrer_policy' => '',
				),
			);
		}

		return null;
	}
}
