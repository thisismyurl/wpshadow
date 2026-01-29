<?php
/**
 * Largest Contentful Paint Analysis
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_LCP_Analysis extends Diagnostic_Base {

	protected static $slug        = 'lcp-analysis';
	protected static $title       = 'Largest Contentful Paint Analysis';
	protected static $description = 'Identifies poor LCP scores';
	protected static $family      = 'performance';

	public static function check() {
		$cache_key = 'wpshadow_lcp_analysis';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Check homepage LCP (simulated analysis).
		$homepage_url = home_url();
		$response     = wp_remote_get( $homepage_url, array( 'timeout' => 10 ) );

		if ( is_wp_error( $response ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$html = wp_remote_retrieve_body( $response );
		$issues = array();

		// Check for hero images without optimization.
		if ( preg_match_all( '/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches ) ) {
			if ( count( $matches[0] ) > 0 ) {
				$first_image = $matches[0][0];
				if ( ! preg_match( '/loading=["\']lazy["\']/', $first_image ) ) {
					$issues[] = 'First image not optimized (no lazy loading skip)';
				}
			}
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'LCP optimization issues detected. Improve loading speed.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-lcp',
				'data'         => array(
					'issues' => $issues,
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
