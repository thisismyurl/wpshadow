<?php
/**
 * Media Sitemap Generation Diagnostic
 *
 * Verifies media files are included in XML sitemaps
 * and checks image sitemap functionality.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Sitemap_Generation Class
 *
 * Checks for image sitemap providers or SEO plugin support.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Media_Sitemap_Generation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-sitemap-generation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Sitemap Generation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies media files are included in XML sitemaps';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$seo_plugins = array(
			'wordpress-seo/wp-seo.php',
			'rank-math/rank-math.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'autodescription/autodescription.php',
			'seopress/seopress.php',
		);

		$seo_active = false;
		foreach ( $seo_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$seo_active = true;
				break;
			}
		}

		$image_sitemap_provider = false;
		if ( function_exists( 'wp_sitemaps_get_server' ) ) {
			$server    = wp_sitemaps_get_server();
			$providers = array();

			if ( is_object( $server ) && isset( $server->registry ) && is_object( $server->registry ) && method_exists( $server->registry, 'get_providers' ) ) {
				$providers = $server->registry->get_providers();
			}

			foreach ( (array) $providers as $name => $provider ) {
				if ( is_string( $name ) && false !== strpos( $name, 'image' ) ) {
					$image_sitemap_provider = true;
					break;
				}

				if ( is_object( $provider ) && false !== stripos( get_class( $provider ), 'image' ) ) {
					$image_sitemap_provider = true;
					break;
				}
			}
		}

		if ( ! $seo_active && ! $image_sitemap_provider && ! has_filter( 'wp_sitemaps_add_provider' ) ) {
			$issues[] = __( 'No image sitemap provider detected; media files may be missing from sitemaps', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-sitemap-generation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
