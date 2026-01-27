<?php
/**
 * Diagnostic: oEmbed Provider Accessibility
 *
 * Checks if oEmbed providers are accessible and configured.
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
 * Class Diagnostic_Oembed_Provider_Access
 *
 * Tests oEmbed provider endpoint availability.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Oembed_Provider_Access extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'oembed-provider-access';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'oEmbed Provider Accessibility';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if oEmbed providers are accessible';

	/**
	 * Check oEmbed provider access.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if WordPress oEmbed endpoint is available.
		$oembed_endpoint = rest_url( 'oembed/1.0/embed' );

		$response = wp_remote_get(
			$oembed_endpoint,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'oEmbed endpoint is not accessible. Embedded content may not work. Check REST API availability.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/oembed_provider_access',
				'meta'        => array(
					'endpoint'      => $oembed_endpoint,
					'error_message' => $response->get_error_message(),
				),
			);
		}

		$status = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $status && 400 !== $status ) { // 400 is OK for oEmbed (missing URL param).
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: HTTP status code */
					__( 'oEmbed endpoint returned HTTP %d. Embedded content functionality may be impaired.', 'wpshadow' ),
					$status
				),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/oembed_provider_access',
				'meta'        => array(
					'endpoint'    => $oembed_endpoint,
					'http_status' => $status,
				),
			);
		}

		return null;
	}
}
