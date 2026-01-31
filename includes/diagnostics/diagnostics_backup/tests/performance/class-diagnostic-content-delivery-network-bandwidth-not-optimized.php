<?php
/**
 * Content Delivery Network Bandwidth Not Optimized Diagnostic
 *
 * Checks if CDN bandwidth is optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2351
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Delivery Network Bandwidth Not Optimized Diagnostic Class
 *
 * Detects unoptimized CDN bandwidth.
 *
 * @since 1.2601.2351
 */
class Diagnostic_Content_Delivery_Network_Bandwidth_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-delivery-network-bandwidth-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Delivery Network Bandwidth Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CDN bandwidth is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2351
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for CDN plugins
		$cdn_plugins = array(
			'cdn-enabler/cdn-enabler.php',
			'w3-total-cache/w3-total-cache.php',
			'sucuri/sucuri.php',
		);

		$cdn_active = false;
		foreach ( $cdn_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$cdn_active = true;
				break;
			}
		}

		if ( ! $cdn_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'CDN bandwidth is not optimized. Use a CDN to serve static assets from edge servers closer to users.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/content-delivery-network-bandwidth-not-optimized',
			);
		}

		return null;
	}
}
