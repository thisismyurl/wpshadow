<?php
/**
 * CNAME Records & CDN Diagnostic
 *
 * Validates that CDN hostnames are backed by CNAME records when in use.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.0900
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_URL_And_Pattern_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CNAME Records & CDN Diagnostic Class
 *
 * Checks for a CDN hostname and validates CNAME records when required.
 *
 * @since 1.6035.0900
 */
class Diagnostic_CNAME_Records_CDN extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cname-records-cdn';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CNAME Records & CDN';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates CDN hostnames are configured with CNAME records';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.0900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$upload_dir = wp_upload_dir();
		$site_host  = Diagnostic_URL_And_Pattern_Helper::get_domain( home_url() );
		$cdn_host   = Diagnostic_URL_And_Pattern_Helper::get_domain( $upload_dir['baseurl'] );

		if ( empty( $cdn_host ) || empty( $site_host ) ) {
			return null;
		}

		$cdn_plugins = array(
			'cloudflare/cloudflare.php' => 'Cloudflare',
			'wp-fastest-cache/wpFastestCache.php' => 'WP Fastest Cache',
			'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
			'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
		);

		$active_cdn_plugin = self::get_first_active_plugin( $cdn_plugins );
		$is_cdn_in_use = ( $cdn_host !== $site_host );

		if ( ! $is_cdn_in_use && ! $active_cdn_plugin ) {
			return null;
		}

		if ( ! function_exists( 'dns_get_record' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'CNAME validation is unavailable because dns_get_record is disabled.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cname-records-cdn',
			);
		}

		$records = @dns_get_record( $cdn_host, DNS_CNAME );
		if ( empty( $records ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'A CDN hostname is in use, but no CNAME records were found. DNS may not be pointing to the CDN provider.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cname-records-cdn',
				'meta'         => array(
					'cdn_host' => $cdn_host,
					'site_host' => $site_host,
					'active_plugin' => $active_cdn_plugin,
				),
			);
		}

		return null;
	}

	/**
	 * Get the first active plugin from a list.
	 *
	 * @since  1.6035.0900
	 * @param  array $plugins Plugin list (file => label).
	 * @return string|null Active plugin label or null.
	 */
	private static function get_first_active_plugin( array $plugins ): ?string {
		foreach ( $plugins as $plugin => $label ) {
			if ( is_plugin_active( $plugin ) ) {
				return $label;
			}
		}

		return null;
	}
}
