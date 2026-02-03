<?php
/**
 * Social Media Images Not Optimized Diagnostic
 *
 * Detects when posts lack properly sized social media images
 * (Open Graph, Twitter Cards), resulting in poor social sharing.
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
 * Social Media Images Not Optimized Diagnostic Class
 *
 * Checks if posts have properly sized social media images. Social
 * platforms require specific dimensions for optimal display.
 *
 * @since 1.6033.1430
 */
class Diagnostic_Social_Media_Images_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-media-images-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Social Media Images Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects posts missing or improperly sized social media images';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if posts have social media images (og:image, twitter:image).
	 * Properly sized images improve CTR by up to 40%.
	 *
	 * @since  1.6033.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Don't flag if Media-Image is already active.
		if ( Upgrade_Path_Helper::has_pro_product( 'wpadmin-media-image' ) ) {
			return null;
		}

		// Don't flag if Yoast Premium or RankMath Pro (includes social image automation).
		if ( defined( 'WPSEO_PREMIUM_FILE' ) || defined( 'RANK_MATH_PRO_FILE' ) ) {
			return null;
		}

		// Count total published posts.
		$total_posts = wp_count_posts( 'post' );
		$published_posts = isset( $total_posts->publish ) ? (int) $total_posts->publish : 0;

		// Don't flag if no posts.
		if ( $published_posts === 0 ) {
			return null;
		}

		// Count posts with social images.
		$posts_with_social_images = self::count_posts_with_social_images();
		$missing_og_images = $published_posts - $posts_with_social_images;

		// Don't flag if most posts have social images (>80%).
		if ( $missing_og_images < ( $published_posts * 0.2 ) ) {
			return null;
		}

		return array(
			'id'                        => self::$slug,
			'title'                     => self::$title,
			'description'               => sprintf(
				/* translators: 1: posts with social images, 2: total posts */
				__( 'Only %1$d of your %2$d posts have custom social images. Properly sized social images improve click-through rates by up to 40%% and ensure professional appearance on Facebook, Twitter, and LinkedIn.', 'wpshadow' ),
				$posts_with_social_images,
				$published_posts
			),
			'severity'                  => 'low',
			'threat_level'              => 20,
			'auto_fixable'              => false,
			'total_posts'               => $published_posts,
			'posts_with_social_images'  => $posts_with_social_images,
			'missing_og_images'         => $missing_og_images,
			'ctr_improvement_potential' => '40%',
			'kb_link'                   => 'https://wpshadow.com/kb/social-image-optimization',
		);
	}

	/**
	 * Count posts with social images.
	 *
	 * @since  1.6033.1430
	 * @return int Number of posts with social images.
	 */
	private static function count_posts_with_social_images() {
		global $wpdb;

		// Check for Yoast SEO og:image meta.
		$yoast_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT p.ID) 
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = %s 
				AND p.post_status = %s
				AND pm.meta_key = %s
				AND pm.meta_value != ''",
				'post',
				'publish',
				'_yoast_wpseo_opengraph-image'
			)
		);

		if ( $yoast_count > 0 ) {
			return (int) $yoast_count;
		}

		// Check for RankMath Facebook image.
		$rankmath_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT p.ID) 
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = %s 
				AND p.post_status = %s
				AND pm.meta_key = %s
				AND pm.meta_value != ''",
				'post',
				'publish',
				'rank_math_facebook_image'
			)
		);

		if ( $rankmath_count > 0 ) {
			return (int) $rankmath_count;
		}

		// Check for generic og:image post meta.
		$generic_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT p.ID) 
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = %s 
				AND p.post_status = %s
				AND pm.meta_key = %s
				AND pm.meta_value != ''",
				'post',
				'publish',
				'og_image'
			)
		);

		return (int) $generic_count;
	}
}
