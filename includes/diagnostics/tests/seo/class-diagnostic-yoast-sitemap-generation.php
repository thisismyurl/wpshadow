<?php
/**
 * Yoast Sitemap Generation Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1730
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Yoast_Sitemap_Generation extends Diagnostic_Base {

	protected static $slug        = 'yoast-sitemap-generation';
	protected static $title       = 'Yoast SEO Sitemap Generation';
	protected static $description = 'Verifies Yoast sitemap is accessible';
	protected static $family      = 'seo';

	public static function check() {
		$cache_key = 'wpshadow_yoast_sitemap';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		if ( ! defined( 'WPSEO_VERSION' ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$sitemap_url = home_url( 'sitemap_index.xml' );
		$response    = wp_remote_get( $sitemap_url, array( 'timeout' => 10 ) );

		if ( is_wp_error( $response ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Yoast sitemap is not accessible. Fix for proper SEO indexing.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/seo-yoast-sitemap',
				'data'         => array(
					'error' => $response->get_error_message(),
				),
			);

			set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );
			return $result;
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $status_code ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: status code */
					__( 'Yoast sitemap returns %d status. Should return 200.', 'wpshadow' ),
					$status_code
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/seo-yoast-sitemap',
				'data'         => array(
					'status_code' => $status_code,
				),
			);

			set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
