<?php
/**
 * Diagnostic: Query String Stripping
 *
 * Checks if query strings on static assets are being stripped (may break cache busting).
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Query_String_Stripping
 *
 * Tests if query strings on static assets are preserved.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Query_String_Stripping extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'query-string-stripping';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Query String Stripping';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if query strings on static assets are being stripped';

	/**
	 * Check query string handling on static asset.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$asset_url = includes_url( 'js/wp-emoji-release.min.js' );
		if ( empty( $asset_url ) ) {
			return null;
		}

		$test_url = add_query_arg( 'ver', get_bloginfo( 'version' ), $asset_url );
		$response = wp_remote_head( $test_url );

		if ( is_wp_error( $response ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Could not verify query string handling because the asset request failed.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/query_string_stripping',
				'meta'        => array(
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
					__( 'Static asset with query string returned HTTP %d. Query stripping or caching rules may be blocking assets with version parameters, which can break cache busting.', 'wpshadow' ),
					$status
				),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/query_string_stripping',
				'meta'        => array(
					'test_url' => $test_url,
					'http_status' => $status,
				),
			);
		}

		return null;
	}
}
