<?php
/**
 * AIOSEO Sitemap Diagnostic
 *
 * Validates XML sitemap configuration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1805
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AIOSEO Sitemap Class
 *
 * Checks sitemap configuration and functionality.
 *
 * @since 1.5029.1805
 */
class Diagnostic_AIOSEO_Sitemap extends Diagnostic_Base {

	protected static $slug        = 'aioseo-sitemap';
	protected static $title       = 'AIOSEO Sitemap Configuration';
	protected static $description = 'Validates XML sitemap';
	protected static $family      = 'plugins';

	public static function check() {
		if ( ! function_exists( 'aioseo' ) ) {
			return null;
		}

		$cache_key = 'wpshadow_aioseo_sitemap';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();
		$options = get_option( 'aioseo_options', array() );

		// Check if sitemap is enabled.
		$sitemap_enabled = isset( $options['sitemap']['general']['enable'] ) 
			? $options['sitemap']['general']['enable'] 
			: true;

		if ( ! $sitemap_enabled ) {
			$issues[] = 'XML sitemap is disabled';
		} else {
			// Check if sitemap is accessible.
			$sitemap_url = home_url( '/sitemap.xml' );
			$response = wp_remote_get( $sitemap_url, array( 'timeout' => 10 ) );
			
			if ( is_wp_error( $response ) ) {
				$issues[] = 'Sitemap URL is not accessible: ' . $response->get_error_message();
			} else {
				$code = wp_remote_retrieve_response_code( $response );
				if ( 200 !== $code ) {
					$issues[] = sprintf( 'Sitemap returned HTTP %d status', $code );
				}
			}

			// Check post types included.
			$post_types_enabled = isset( $options['sitemap']['general']['postTypes']['all'] ) 
				? $options['sitemap']['general']['postTypes']['all'] 
				: true;

			if ( ! $post_types_enabled ) {
				$included_types = isset( $options['sitemap']['general']['postTypes']['included'] ) 
					? $options['sitemap']['general']['postTypes']['included'] 
					: array();
				
				if ( empty( $included_types ) ) {
					$issues[] = 'No post types included in sitemap';
				}
			}

			// Check image sitemap.
			$image_sitemap = isset( $options['sitemap']['general']['advancedSettings']['enable'] ) 
				? $options['sitemap']['general']['advancedSettings']['enable'] 
				: true;

			if ( ! $image_sitemap ) {
				$issues[] = 'Image sitemap is disabled';
			}
		}

		// Check if sitemap submitted to search engines.
		$search_console = isset( $options['webmasterTools']['googleAnalytics']['id'] ) 
			? $options['webmasterTools']['googleAnalytics']['id'] 
			: '';

		if ( empty( $search_console ) ) {
			$issues[] = 'Google Search Console not connected - sitemap may not be submitted';
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d sitemap configuration issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-aioseo-sitemap',
				'data'         => array(
					'sitemap_issues' => $issues,
					'total_issues' => count( $issues ),
					'sitemap_url' => $sitemap_url,
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
