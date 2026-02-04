<?php
/**
 * Image Branding Automation Missing Diagnostic
 *
 * Detects when images lack automated branding or watermarking,
 * requiring manual effort and risking inconsistent branding.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Branding Automation Missing Diagnostic Class
 *
 * Checks if images have automated branding/watermarking. For brand
 * protection and consistent visual identity, automated branding saves
 * time and ensures consistency.
 *
 * @since 1.6033.1430
 */
class Diagnostic_Image_Branding_Automation_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-branding-automation-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Automated Image Branding or Watermarking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing automated image branding and watermarking capabilities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media-optimization';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if images have automated branding. Watermarking protects
	 * copyright and maintains brand consistency.
	 *
	 * @since  1.6033.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Don't flag if Media-Image is already active.
		if ( Upgrade_Path_Helper::has_pro_product( 'wpadmin-media-image' ) ) {
			return null;
		}

		// Check for existing watermark plugins.
		if ( self::has_watermark_plugin() ) {
			return null;
		}

		// Count total images.
		global $wpdb;
		$total_images = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_mime_type LIKE 'image/%'"
		);

		// Don't flag if no images.
		if ( $total_images === 0 ) {
			return null;
		}

		// Count social images (og:image meta tags).
		$social_images_count = self::count_social_images();

		// Check if site would benefit from branding (photography, portfolio, etc.).
		$high_need = self::has_high_branding_need();

		// Only flag if there are many images or high need.
		if ( $total_images < 50 && ! $high_need ) {
			return null;
		}

		return array(
			'id'                 => self::$slug,
			'title'              => self::$title,
			'description'        => sprintf(
				/* translators: %d: number of images */
				__( 'Your %d images lack consistent branding. Automated watermarking and social image generation would save time, protect content, and maintain brand consistency.', 'wpshadow' ),
				$total_images
			),
			'severity'           => 'low',
			'threat_level'       => 15,
			'auto_fixable'       => false,
			'total_images'       => (int) $total_images,
			'social_images_count' => $social_images_count,
			'branded_images'     => 0,
			'kb_link'            => 'https://wpshadow.com/kb/image-branding',
		);
	}

	/**
	 * Check if watermark plugin is already active.
	 *
	 * @since  1.6033.1430
	 * @return bool True if watermark plugin detected.
	 */
	private static function has_watermark_plugin() {
		$watermark_plugins = array(
			'easy-watermark/index.php',
			'image-watermark/image-watermark.php',
			'watermark-images-for-wp-and-woo-grandpluginswp/main.php',
			'wp-watermark/wp-watermark.php',
		);

		foreach ( $watermark_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Count posts with social images (og:image).
	 *
	 * @since  1.6033.1430
	 * @return int Number of posts with social images.
	 */
	private static function count_social_images() {
		// Check for Yoast SEO meta.
		global $wpdb;
		$count = $wpdb->get_var(
			"SELECT COUNT(DISTINCT post_id) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_yoast_wpseo_opengraph-image'"
		);

		if ( $count > 0 ) {
			return (int) $count;
		}

		// Check for RankMath meta.
		$count = $wpdb->get_var(
			"SELECT COUNT(DISTINCT post_id) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = 'rank_math_facebook_image'"
		);

		return (int) $count;
	}

	/**
	 * Check if site has high branding need.
	 *
	 * @since  1.6033.1430
	 * @return bool True if high branding need detected.
	 */
	private static function has_high_branding_need() {
		// Check active theme (photography/portfolio themes).
		$theme = wp_get_theme();
		$theme_name = strtolower( $theme->get( 'Name' ) );

		$branding_keywords = array( 'photo', 'portfolio', 'gallery', 'studio', 'creative' );
		foreach ( $branding_keywords as $keyword ) {
			if ( strpos( $theme_name, $keyword ) !== false ) {
				return true;
			}
		}

		// Check for photography/portfolio plugins.
		$portfolio_plugins = array(
			'envira-gallery-lite/envira-gallery-lite.php',
			'nextgen-gallery/nggallery.php',
			'modula-best-grid-gallery/Modula.php',
		);

		foreach ( $portfolio_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}
}
