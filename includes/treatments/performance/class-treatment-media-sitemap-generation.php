<?php
/**
 * Media Sitemap Generation Treatment
 *
 * Verifies media files are included in XML sitemaps
 * and checks image sitemap functionality.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1625
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Sitemap_Generation Class
 *
 * Checks for image sitemap providers or SEO plugin support.
 *
 * @since 1.6033.1625
 */
class Treatment_Media_Sitemap_Generation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-sitemap-generation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Sitemap Generation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies media files are included in XML sitemaps';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1625
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
			$providers = wp_sitemaps_get_server()->get_providers();
			foreach ( $providers as $name => $provider ) {
				if ( false !== strpos( $name, 'image' ) ) {
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
				'kb_link'      => 'https://wpshadow.com/kb/media-sitemap-generation',
			);
		}

		return null;
	}
}
