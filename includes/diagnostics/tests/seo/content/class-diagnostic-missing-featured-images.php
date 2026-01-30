<?php
/**
 * Missing Featured Images Diagnostic
 *
 * Identifies posts without featured images that affect social sharing,
 * visual appeal, and user engagement on blog/archive pages.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Missing_Featured_Images Class
 *
 * Detects posts missing featured images.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Missing_Featured_Images extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-featured-images';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Featured Images';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies posts without featured images';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if missing images found, null otherwise.
	 */
	public static function check() {
		$missing_images = self::check_missing_featured_images();

		if ( $missing_images['count'] === 0 ) {
			return null; // All posts have featured images
		}

		$percentage = $missing_images['total'] > 0 ? round( ( $missing_images['count'] / $missing_images['total'] ) * 100 ) : 0;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: number of posts, 2: percentage */
				__( '%1$d posts (%2$d%%) missing featured images. Social media shares show blank previews, blog pages look unprofessional.', 'wpshadow' ),
				$missing_images['count'],
				$percentage
			),
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/featured-images',
			'family'       => self::$family,
			'meta'         => array(
				'missing_count'      => $missing_images['count'],
				'total_posts'        => $missing_images['total'],
				'percentage_missing' => $percentage . '%',
				'social_impact'      => __( 'Facebook/Twitter show blank previews' ),
				'visual_appeal'      => __( 'Blog page looks unfinished' ),
			),
			'details'      => array(
				'why_featured_images_important' => array(
					__( 'Social shares: Facebook/Twitter use featured image for preview' ),
					__( 'Blog archives: Visual hierarchy helps scanning' ),
					__( 'Click-through: Posts with images get 94% more views' ),
					__( 'Professionalism: Blank spaces look unfinished' ),
				),
				'where_featured_images_appear' => array(
					'Blog Homepage' => 'Grid of posts with thumbnails',
					'Category Archives' => 'Post listings with images',
					'Social Media Shares' => 'Facebook/Twitter/LinkedIn preview cards',
					'RSS Feeds' => 'Email subscribers see featured image',
					'Related Posts' => 'Sidebar widgets showing thumbnails',
				),
				'finding_missing_images'   => array(
					'WordPress Admin' => array(
						'Posts → All Posts',
						'Add "Featured Image" column',
						'Filter by posts with no thumbnail',
					),
					'Plugin: Auto Featured Image' => array(
						'Automatically sets first image as featured',
						'Runs on publish or retroactively',
						'Free plugin',
					),
				),
				'adding_featured_images'   => array(
					'Manual Method' => array(
						'Edit post',
						'Right sidebar → Featured Image → Set featured image',
						'Choose from Media Library or upload',
						'Ideal size: 1200x630px for social sharing',
					),
					'Bulk Method' => array(
						'Install: Quick Featured Images plugin',
						'Select multiple posts',
						'Bulk assign featured images',
						'Can use first post image automatically',
					),
				),
				'featured_image_best_practices' => array(
					'Dimensions' => '1200x630px (Facebook/Twitter optimal)',
					'File Size' => '<200KB (fast loading)',
					'Format' => 'JPG (photos) or PNG (graphics)',
					'Alt Text' => 'Always add for accessibility',
					'Consistency' => 'Same aspect ratio across all posts',
				),
				'automation_options'       => array(
					'Auto Featured Image (Free)' => array(
						'Sets first image in content as featured',
						'Runs automatically on publish',
						'Retroactive scan for old posts',
					),
					'Default Featured Image (Free)' => array(
						'Fallback if no featured image set',
						'Shows your default/logo instead of blank',
						'Quick fix for visual consistency',
					),
				),
			),
		);
	}

	/**
	 * Check for missing featured images.
	 *
	 * @since  1.2601.2148
	 * @return array Missing featured image statistics.
	 */
	private static function check_missing_featured_images() {
		global $wpdb;

		$total = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type = 'post'"
		);

		$with_thumbnail = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.ID) 
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_status = 'publish'
			AND p.post_type = 'post'
			AND pm.meta_key = '_thumbnail_id'
			AND pm.meta_value != ''"
		);

		return array(
			'count' => max( 0, $total - $with_thumbnail ),
			'total' => $total,
		);
	}
}
