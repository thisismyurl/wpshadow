<?php
/**
 * Diagnostic: X-Content-Type-Options Header
 *
 * Checks if the X-Content-Type-Options security header is present.
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
 * Class Diagnostic_X_Content_Type_Options_Header
 *
 * Tests for X-Content-Type-Options header presence.
 *
 * @since 1.2601.2148
 */
class Diagnostic_X_Content_Type_Options_Header extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'x-content-type-options-header';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'X-Content-Type-Options Header';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if X-Content-Type-Options security header is present';

	/**
	 * Check X-Content-Type-Options header.
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
				'description' => __( 'Could not check X-Content-Type-Options header because the request failed.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/x_content_type_options_header',
				'meta'        => array(
					'error_message' => $response->get_error_message(),
				),
			);
		}

		$headers = wp_remote_retrieve_headers( $response );
		$header  = $headers['x-content-type-options'] ?? null;

		if ( empty( $header ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'X-Content-Type-Options header is missing. Add "X-Content-Type-Options: nosniff" to prevent MIME type sniffing attacks.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/x_content_type_options_header',
				'meta'        => array(
					'x_content_type_options' => '',
				),
			);
		}

		if ( 'nosniff' !== strtolower( $header ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Current header value */
					__( 'X-Content-Type-Options header is set but not to "nosniff" (current: %s). Should be "nosniff" to prevent MIME sniffing.', 'wpshadow' ),
					$header
				),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/x_content_type_options_header',
				'meta'        => array(
					'x_content_type_options' => $header,
				),
			);
		}

		return null;
	}
}
