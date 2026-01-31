<?php
/**
 * All In One Seo Image Seo Diagnostic
 *
 * All In One Seo Image Seo configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.705.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All In One Seo Image Seo Diagnostic Class
 *
 * @since 1.705.0000
 */
class Diagnostic_AllInOneSeoImageSeo extends Diagnostic_Base {

	protected static $slug = 'all-in-one-seo-image-seo';
	protected static $title = 'All In One Seo Image Seo';
	protected static $description = 'All In One Seo Image Seo configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'aioseo' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Image alt text missing.
		global $wpdb;
		$missing_alt = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_mime_type LIKE %s AND ID NOT IN (
					SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != ''
				)",
				'attachment',
				'image/%',
				'_wp_attachment_image_alt'
			)
		);
		if ( $missing_alt > 0 ) {
			$issues[] = "{$missing_alt} images missing alt text (SEO and accessibility issue)";
		}

		// Check 2: Image title attributes.
		$auto_generate_titles = get_option( 'aioseo_image_title_attr', '0' );
		if ( '0' === $auto_generate_titles ) {
			$issues[] = 'image title attributes not auto-generated (missing SEO metadata)';
		}

		// Check 3: Image sitemap enabled.
		$image_sitemap = get_option( 'aioseo_image_sitemap_enabled', '0' );
		if ( '0' === $image_sitemap ) {
			$issues[] = 'image sitemap disabled (Google cannot discover images)';
		}

		// Check 4: Open Graph image settings.
		$og_image_default = get_option( 'aioseo_og_default_image', '' );
		if ( empty( $og_image_default ) ) {
			$issues[] = 'no default Open Graph image (social sharing may show no image)';
		}

		// Check 5: Image file name optimization.
		$optimize_filenames = get_option( 'aioseo_optimize_image_filenames', '0' );
		if ( '0' === $optimize_filenames ) {
			$issues[] = 'image filename optimization disabled (SEO opportunity missed)';
		}

		// Check 6: Schema.org image markup.
		$schema_images = get_option( 'aioseo_schema_images', '0' );
		if ( '0' === $schema_images ) {
			$issues[] = 'schema.org image markup disabled (rich results may not show images)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'All-in-One SEO image optimization issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-seo-image-seo',
			);
		}

		return null;
	}
}
