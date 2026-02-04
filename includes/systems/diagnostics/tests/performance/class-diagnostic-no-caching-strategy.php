<?php
/**
 * No Caching Strategy Diagnostic
 *
 * Detects when caching is not implemented,
 * causing unnecessarily slow page loads and poor performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Caching Strategy
 *
 * Checks whether page caching, browser caching, or object caching
 * is implemented for performance optimization.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Caching_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-caching-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Caching Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether caching is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for caching plugins
		$has_caching = is_plugin_active( 'wp-super-cache/wp-cache.php' ) ||
			is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ||
			is_plugin_active( 'wp-fastest-cache/wpfc.php' ) ||
			is_plugin_active( 'litespeed-cache/litespeed-cache.php' );

		// Check for server-level caching headers
		$homepage = wp_remote_head( home_url() );
		if ( ! is_wp_error( $homepage ) ) {
			$headers = wp_remote_retrieve_headers( $homepage );
			$has_cache_headers = isset( $headers['cache-control'] ) || isset( $headers['expires'] );
		} else {
			$has_cache_headers = false;
		}

		if ( ! $has_caching && ! $has_cache_headers ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not caching at all, which means every page load requires processing WordPress from scratch. Think of caching as pre-making popular meals instead of cooking every order fresh. Without caching, pages that should load in 100ms take 1-2 seconds. Caching makes pages load 5-10x faster with minimal effort. Three types: page cache (cache entire page), browser cache (browsers cache static assets), object cache (cache database queries).',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Page Load Performance',
					'potential_gain' => '5-10x faster page loads',
					'roi_explanation' => 'Caching eliminates processing overhead, making pages 5-10x faster, directly improving SEO rankings and conversion rates.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/caching-strategy',
			);
		}

		return null;
	}
}
