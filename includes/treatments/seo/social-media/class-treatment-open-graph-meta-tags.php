<?php
/**
 * Open Graph & Meta Tags for Social Sharing
 *
 * Validates proper Open Graph implementation for social media content preview.
 *
 * @since   1.6030.2148
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_HTML_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Open_Graph_Meta_Tags Class
 *
 * Checks if Open Graph meta tags are properly implemented for social media sharing.
 * These tags control how content appears when shared on Facebook, LinkedIn, and other platforms.
 *
 * @since 1.6030.2148
 */
class Treatment_Open_Graph_Meta_Tags extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'open-graph-meta-tags';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Open Graph Meta Tags';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates Open Graph implementation for social sharing';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'social-media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Open_Graph_Meta_Tags' );
	}

	/**
	 * Check if SEO plugin is active.
	 *
	 * @since  1.6030.2148
	 * @return bool True if SEO plugin active.
	 */
	private static function has_seo_plugin() {
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'seo-by-rank-math/rank-math.php',
			'the-seo-framework/the-seo-framework.php',
			'seopress/seopress.php',
		);

		foreach ( $seo_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if social media plugin is active.
	 *
	 * @since  1.6030.2148
	 * @return bool True if social plugin active.
	 */
	private static function has_social_plugin() {
		$social_plugins = array(
			'social-warfare/index.php',
			'social-pug/index.php',
			'jetpack/jetpack.php',
			'sharethis-share-buttons/sharethis.php',
		);

		foreach ( $social_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if site is multilingual.
	 *
	 * @since  1.6030.2148
	 * @return bool True if multilingual plugins detected.
	 */
	private static function is_multilingual_site() {
		$multilingual_plugins = array(
			'polylang/polylang.php',
			'wpml/sitepress.php',
			'translatepress-multilingual/index.php',
		);

		foreach ( $multilingual_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}
}
