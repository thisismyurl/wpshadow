<?php
/**
 * Headless CMS Media Serving Diagnostic
 *
 * Detects if media is properly configured for headless CMS architectures.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.1635
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Headless_CMS_Media_Serving Class
 *
 * Tests if media is properly configured for headless CMS or decoupled
 * architectures, including proper REST API endpoints and media metadata.
 *
 * @since 1.26033.1635
 */
class Diagnostic_Headless_CMS_Media_Serving extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'headless-cms-media-serving';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Headless CMS Media Serving';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies media is properly configured for headless CMS';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.26033.1635
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if REST API is enabled for media
		if ( ! rest_is_enabled() ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'REST API is disabled. Enable it for headless CMS media serving.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/headless-cms-media-serving',
			);
		}

		// Check if CORS headers are properly configured
		$cors_enabled = has_filter( 'wp_headers', 'rest_handle_preflight_request' ) ||
			has_filter( 'rest_pre_serve_request' );

		// Check for media metadata in REST responses
		if ( ! $cors_enabled && function_exists( 'register_rest_field' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'CORS headers may not be properly configured for headless consumers. Add CORS support.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/headless-cms-media-serving',
			);
		}

		// Verify media endpoints are accessible
		if ( ! function_exists( 'wp_rest_server' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'REST API server is not properly initialized. Media endpoints may not be accessible.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/headless-cms-media-serving',
			);
		}

		return null;
	}
}
