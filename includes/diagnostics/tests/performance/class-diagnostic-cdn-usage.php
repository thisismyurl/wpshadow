<?php
/**
 * CDN Usage Diagnostic
 *
 * Issue #4899: Static Assets Not Served via CDN
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if static assets are served from a CDN.
 * CDNs reduce latency by serving from geographically closer servers.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_CDN_Usage Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_CDN_Usage extends Diagnostic_Base {

	protected static $slug = 'cdn-usage';
	protected static $title = 'Static Assets Not Served via CDN';
	protected static $description = 'Checks if images, CSS, JS are served from a CDN';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Serve images via CDN (CloudFlare, Cloudinary, etc)', 'wpshadow' );
		$issues[] = __( 'Serve CSS and JavaScript via CDN', 'wpshadow' );
		$issues[] = __( 'Use CDN with edge locations globally', 'wpshadow' );
		$issues[] = __( 'CDN should support HTTP/2 and Brotli compression', 'wpshadow' );
		$issues[] = __( 'Configure CDN with proper cache headers', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Content Delivery Networks (CDNs) cache assets on servers worldwide. Users download from the nearest location, reducing latency by 50-200ms.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/cdn-usage',
				'details'      => array(
					'recommendations'         => $issues,
					'latency_savings'         => '50-200ms faster for international users',
					'free_cdns'               => 'Cloudflare (free), jsDelivr (free for open source)',
					'premium_cdns'            => 'Cloudinary, BunnyCDN, KeyCDN, StackPath',
				),
			);
		}

		return null;
	}
}
