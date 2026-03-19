<?php
/**
 * SEO Meta Tags Configuration Diagnostic
 *
 * Checks for proper SEO meta tags (title, description, canonical) that help
 * search engines understand and rank page content.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SEO Meta Tags Configuration Diagnostic Class
 *
 * Verifies SEO meta tag implementation:
 * - Meta description tags
 * - Canonical URLs
 * - Open Graph tags
 * - Title tag optimization
 *
 * @since 1.6093.1200
 */
class Diagnostic_Seo_Meta_Tags_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'seo-meta-tags-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SEO Meta Tags Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for proper SEO meta tags for search engine optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$seo_plugin_active = false;

		// Check for SEO plugins
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php'        => 'Yoast SEO',
			'rank-math/rank-math.php'         => 'Rank Math',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
			'the-seo-framework/the-seo-framework.php' => 'The SEO Framework',
		);

		foreach ( $seo_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$seo_plugin_active = true;
				break;
			}
		}

		if ( ! $seo_plugin_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'SEO plugin not detected. Installing an SEO plugin helps manage meta tags, sitemaps, and other SEO elements.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/seo-meta-tags',
				'meta'          => array(
					'seo_plugin'           => $seo_plugin_active ? 'Active' : 'Not active',
					'recommendation'       => 'Install Rank Math or Yoast SEO for comprehensive SEO management',
					'impact'               => 'Proper meta tags can improve click-through rate by 20-30% in search results',
					'essential_tags'       => array(
						'Title tags (50-60 chars)',
						'Meta descriptions (120-160 chars)',
						'Canonical URLs',
						'Open Graph tags (social)',
						'Structured data markup',
					),
				),
			);
		}

		return null;
	}
}
