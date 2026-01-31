<?php
/**
 * All In One Seo Sitemap Diagnostic
 *
 * All In One Seo Sitemap configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.700.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All In One Seo Sitemap Diagnostic Class
 *
 * @since 1.700.0000
 */
class Diagnostic_AllInOneSeoSitemap extends Diagnostic_Base {

	protected static $slug = 'all-in-one-seo-sitemap';
	protected static $title = 'All In One Seo Sitemap';
	protected static $description = 'All In One Seo Sitemap configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'aioseo' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Sitemap enabled.
		$sitemap_enabled = get_option( 'aioseo_sitemap_enabled', '0' );
		if ( '0' === $sitemap_enabled ) {
			$issues[] = 'XML sitemap disabled (search engines cannot discover content)';
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'AIOSEO XML sitemap is disabled - search engines cannot efficiently discover your content',
				'severity'    => 60,
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-seo-sitemap',
			);
		}

		// Check 2: Conflicting sitemaps.
		$wp_sitemap = get_option( 'wp_sitemap_enabled', '1' );
		if ( '1' === $wp_sitemap && '1' === $sitemap_enabled ) {
			$issues[] = 'both WordPress and AIOSEO sitemaps enabled (causes conflicts)';
		}

		// Check 3: Post types included.
		$included_post_types = get_option( 'aioseo_sitemap_post_types', array() );
		if ( empty( $included_post_types ) ) {
			$issues[] = 'no post types included in sitemap (empty sitemap)';
		}

		// Check 4: Priority and frequency settings.
		$auto_priority = get_option( 'aioseo_sitemap_auto_priority', '0' );
		if ( '0' === $auto_priority ) {
			$issues[] = 'auto-priority disabled (consider enabling for dynamic priority)';
		}

		// Check 5: Large sitemap without pagination.
		global $wpdb;
		$post_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = %s AND post_type IN ('post', 'page')",
				'publish'
			)
		);
		$per_page = get_option( 'aioseo_sitemap_per_page', 1000 );
		if ( $post_count > 1000 && $per_page >= 1000 ) {
			$issues[] = "{$post_count} posts in sitemap (consider reducing per-page limit for performance)";
		}

		// Check 6: Image sitemap.
		$image_sitemap = get_option( 'aioseo_sitemap_images', '0' );
		if ( '0' === $image_sitemap ) {
			$issues[] = 'image sitemap disabled (images not indexed efficiently)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'All-in-One SEO sitemap configuration issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-seo-sitemap',
			);
		}

		return null;
	}
}
