<?php
/**
 * Yoast SEO vs Rank Math Conflict Detection Diagnostic
 *
 * Detect if multiple SEO plugins installed causing conflicts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SEO
 * @since      1.6030.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SEO Plugin Conflict Detection Diagnostic Class
 *
 * @since 1.6030.1300
 */
class Diagnostic_SeoPluginConflict extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'seo-plugin-conflict';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Yoast SEO vs Rank Math Conflict Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detect if multiple SEO plugins installed causing conflicts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.1300
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check 1: Check for multiple active SEO plugins
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php'                    => 'Yoast SEO',
			'seo-by-rank-math/rank-math.php'              => 'Rank Math',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
			'squirrly-seo/squirrly.php'                   => 'Squirrly SEO',
			'seo-ultimate/seo-ultimate.php'               => 'SEO Ultimate',
			'autodescription/autodescription.php'         => 'The SEO Framework',
			'wp-seopress/seopress.php'                    => 'SEOPress',
		);

		$active_seo_plugins = array();
		foreach ( $seo_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_seo_plugins[] = $plugin_name;
			}
		}

		if ( count( $active_seo_plugins ) > 1 ) {
			$issues[] = sprintf( 'multiple SEO plugins active: %s', implode( ', ', $active_seo_plugins ) );
		} elseif ( count( $active_seo_plugins ) === 0 ) {
			// No SEO plugin conflict to check
			return null;
		}

		// Check 2: Verify no conflicting XML sitemaps
		$sitemap_endpoints = array();

		if ( defined( 'WPSEO_VERSION' ) ) {
			$sitemap_endpoints[] = 'sitemap_index.xml (Yoast)';
		}

		if ( class_exists( 'RankMath' ) || defined( 'RANK_MATH_VERSION' ) ) {
			$sitemap_endpoints[] = 'sitemap_index.xml (Rank Math)';
		}

		if ( class_exists( 'AIOSEO\Plugin\Common\Models\Sitemap' ) ) {
			$sitemap_endpoints[] = 'sitemap.xml (AIOSEO)';
		}

		if ( count( $sitemap_endpoints ) > 1 ) {
			$issues[] = sprintf( 'conflicting sitemaps: %s', implode( ', ', $sitemap_endpoints ) );
		}

		// Check 3: Test for duplicate meta tags
		$meta_tag_sources = 0;

		if ( defined( 'WPSEO_VERSION' ) ) {
			$meta_tag_sources++;
		}

		if ( class_exists( 'RankMath' ) ) {
			$meta_tag_sources++;
		}

		if ( function_exists( 'aioseo' ) ) {
			$meta_tag_sources++;
		}

		if ( $meta_tag_sources > 1 ) {
			$issues[] = sprintf( '%d plugins outputting meta tags (causes duplicates)', $meta_tag_sources );
		}

		// Check 4: Check for conflicting schema markup
		$schema_sources = 0;

		if ( defined( 'WPSEO_VERSION' ) ) {
			$yoast_schema = get_option( 'wpseo_social', array() );
			if ( isset( $yoast_schema['og_default_image'] ) ) {
				$schema_sources++;
			}
		}

		if ( class_exists( 'RankMath' ) ) {
			$schema_sources++;
		}

		if ( $schema_sources > 1 ) {
			$issues[] = sprintf( '%d plugins generating schema markup (causes conflicts)', $schema_sources );
		}

		// Check 5: Verify no double Open Graph tags
		$og_sources = 0;

		if ( defined( 'WPSEO_VERSION' ) ) {
			$og_enabled = get_option( 'wpseo_social', array() );
			if ( ! isset( $og_enabled['opengraph'] ) || true === $og_enabled['opengraph'] ) {
				$og_sources++;
			}
		}

		if ( class_exists( 'RankMath' ) ) {
			$og_sources++;
		}

		if ( function_exists( 'jetpack_og_tags' ) ) {
			$og_sources++;
		}

		if ( $og_sources > 1 ) {
			$issues[] = sprintf( '%d sources outputting Open Graph tags', $og_sources );
		}

		// Check 6: Test for performance impact of multiple plugins
		if ( count( $active_seo_plugins ) > 1 ) {
			/**
			 * NOTE: Using $wpdb for multi-pattern COUNT() query is intentional.
			 *
			 * WordPress API alternative: Individual get_option() calls
			 * Not suitable because:
			 * - We need aggregate count across multiple plugin prefixes
			 * - Don't know specific option names in advance
			 * - Pattern matching with OR conditions not supported by WordPress API
			 * - Single COUNT() query is more efficient than iterating options
			 */
			global $wpdb;
			$seo_options_count = $wpdb->get_var(
				"SELECT COUNT(*)
				FROM {$wpdb->options}
				WHERE option_name LIKE 'wpseo%'
				   OR option_name LIKE 'rank_math%'
				   OR option_name LIKE 'aioseo%'"
			);

			if ( $seo_options_count > 100 ) {
				$issues[] = sprintf( '%d SEO-related options (performance overhead)', $seo_options_count );
			}
		}

		// Return finding if issues exist
		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 65 + ( count( $issues ) * 5 ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'SEO plugin conflicts detected: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/seo-plugin-conflict',
			);
		}

		return null;
	}
}
