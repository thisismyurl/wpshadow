<?php
/**
 * No CDN (Content Delivery Network) Diagnostic
 *
 * Detects when CDN is not configured,
 * causing slower page loads for global visitors.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No CDN (Content Delivery Network)
 *
 * Checks whether a CDN is configured to serve
 * static assets from edge locations.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_CDN_Content_Delivery_Network extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-cdn-content-delivery-network';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CDN (Content Delivery Network)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether CDN is configured';

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
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for CDN plugins
		$has_cdn = is_plugin_active( 'cloudflare/cloudflare.php' ) ||
			is_plugin_active( 'cdn-enabler/cdn-enabler.php' ) ||
			is_plugin_active( 'w3-total-cache/w3-total-cache.php' );

		// Check homepage for CDN URLs
		$homepage = wp_remote_get( home_url() );
		if ( ! is_wp_error( $homepage ) ) {
			$body = wp_remote_retrieve_body( $homepage );
			$has_cdn_urls = preg_match( '/cdn\.|cloudflare\.|cloudfront\./i', $body );
		} else {
			$has_cdn_urls = false;
		}

		if ( ! $has_cdn && ! $has_cdn_urls ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not using a CDN, which means all visitors load content from one server location. CDNs copy your static files (images, CSS, JavaScript) to servers worldwide. When someone visits from Australia, they load files from Sydney instead of New York—making pages load 2-5x faster. CDNs also reduce server load (fewer requests to handle) and improve reliability (if one location fails, others still work). Many CDNs have free tiers.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Global Page Load Speed',
					'potential_gain' => '2-5x faster for international visitors',
					'roi_explanation' => 'CDN serves content from edge locations near users, making pages 2-5x faster globally while reducing server load.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/cdn-content-delivery-network?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
