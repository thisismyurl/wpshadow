<?php
/**
 * Content Delivery Network Integration Not Configured Diagnostic
 *
 * Checks if CDN is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Delivery Network Integration Not Configured Diagnostic Class
 *
 * Detects missing CDN integration.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Content_Delivery_Network_Integration_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-delivery-network-integration-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Delivery Network Integration Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CDN is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for CDN integration
		if ( ! defined( 'CDN_URL' ) && ! has_filter( 'content_url', 'wp_cdn_filter_content_url' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Content Delivery Network integration is not configured. Integrate a CDN like Cloudflare, Bunny CDN, or Amazon CloudFront for faster global content delivery.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/content-delivery-network-integration-not-configured',
			);
		}

		return null;
	}
}
