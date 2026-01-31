<?php
/**
 * Static Asset Caching Headers Diagnostic
 *
 * Checks if CSS/JS/images have proper Cache-Control headers.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1150
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Static Asset Caching Headers Class
 *
 * Validates Cache-Control headers for static assets.
 * Proper caching reduces load times by 30-50%.
 *
 * @since 1.5029.1150
 */
class Diagnostic_Asset_Caching extends Diagnostic_Base {

	protected static $slug        = 'asset-caching-headers';
	protected static $title       = 'Static Asset Caching Headers';
	protected static $description = 'Validates Cache-Control headers for performance';
	protected static $family      = 'performance';

	public static function check() {
		$cache_key = 'wpshadow_asset_caching_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();
		$assets_without_cache = array();

		// Check enqueued assets using WordPress API (NO $wpdb).
		global $wp_styles, $wp_scripts;

		if ( ! $wp_styles instanceof \WP_Styles ) {
			wp_styles();
		}
		if ( ! $wp_scripts instanceof \WP_Scripts ) {
			wp_scripts();
		}

		$assets_to_check = array();

		// Collect CSS.
		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( ! empty( $style->src ) ) {
				$assets_to_check[] = array( 'type' => 'css', 'handle' => $handle, 'src' => $style->src );
			}
		}

		// Collect JS.
		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( ! empty( $script->src ) ) {
				$assets_to_check[] = array( 'type' => 'js', 'handle' => $handle, 'src' => $script->src );
			}
		}

		// Sample 5 assets to avoid long execution.
		$sample_assets = array_slice( $assets_to_check, 0, 5 );

		foreach ( $sample_assets as $asset ) {
			$response = wp_remote_head( $asset['src'], array( 'timeout' => 5 ) );

			if ( is_wp_error( $response ) ) {
				continue;
			}

			$headers = wp_remote_retrieve_headers( $response );
			$cache_control = $headers['cache-control'] ?? '';

			// Check cache duration.
			if ( empty( $cache_control ) ) {
				$assets_without_cache[] = array(
					'handle' => $asset['handle'],
					'type'   => $asset['type'],
					'issue'  => 'No Cache-Control header',
				);
			} elseif ( ! preg_match( '/max-age=(\d+)/', $cache_control, $matches ) ) {
				$assets_without_cache[] = array(
					'handle' => $asset['handle'],
					'type'   => $asset['type'],
					'issue'  => 'No max-age directive',
				);
			} elseif ( (int) $matches[1] < 604800 ) { // Less than 7 days.
				$assets_without_cache[] = array(
					'handle'  => $asset['handle'],
					'type'    => $asset['type'],
					'max_age' => (int) $matches[1],
					'issue'   => 'Cache duration too short (< 7 days)',
				);
			}
		}

		if ( count( $assets_without_cache ) > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of assets */
				__( '%d assets have insufficient caching headers', 'wpshadow' ),
				count( $assets_without_cache )
			);
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of assets */
					__( '%d static assets lack proper caching. Proper headers reduce load times by 30-50%%.', 'wpshadow' ),
					count( $assets_without_cache )
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/performance-asset-caching-headers',
				'data'         => array(
					'assets_without_cache' => $assets_without_cache,
					'sampled_count'        => count( $sample_assets ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
