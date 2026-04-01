<?php
/**
 * Image SEO Optimization Not Implemented Diagnostic
 *
 * Checks if image SEO is optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image SEO Optimization Not Implemented Diagnostic Class
 *
 * Detects unoptimized image SEO.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Image_SEO_Optimization_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-seo-optimization-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image SEO Optimization Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if image SEO is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Sample recent images for SEO analysis.
		global $wpdb;

		$recent_images = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title, post_name, guid FROM {$wpdb->posts}
				 WHERE post_type = %s
				 AND post_mime_type LIKE %s
				 AND post_status = %s
				 ORDER BY post_date DESC
				 LIMIT 50",
				'attachment',
				'image%',
				'inherit'
			)
		);

		$total_images           = count( $recent_images );
		$images_with_alt        = 0;
		$images_with_title      = 0;
		$images_with_caption    = 0;
		$images_with_bad_filename = 0;

		foreach ( $recent_images as $image ) {
			// Check alt text.
			$alt_text = get_post_meta( $image->ID, '_wp_attachment_image_alt', true );
			if ( ! empty( $alt_text ) ) {
				$images_with_alt++;
			}

			// Check title.
			if ( ! empty( $image->post_title ) && $image->post_title !== 'Auto Draft' ) {
				$images_with_title++;
			}

			// Check for caption (post_excerpt).
			$caption = $wpdb->get_var( $wpdb->prepare( "SELECT post_excerpt FROM {$wpdb->posts} WHERE ID = %d", $image->ID ) );
			if ( ! empty( $caption ) ) {
				$images_with_caption++;
			}

			// Check filename (common bad patterns).
			$filename = basename( $image->guid );
			if ( preg_match( '/^(img|image|photo|pic|screenshot|untitled|dsc|_mg_|[0-9]+)[-_]?[0-9]*\.(jpg|jpeg|png|gif|webp)$/i', $filename ) ) {
				$images_with_bad_filename++;
			}
		}

		$alt_percentage      = $total_images > 0 ? round( ( $images_with_alt / $total_images ) * 100 ) : 0;
		$title_percentage    = $total_images > 0 ? round( ( $images_with_title / $total_images ) * 100 ) : 0;
		$bad_filename_percentage = $total_images > 0 ? round( ( $images_with_bad_filename / $total_images ) * 100 ) : 0;

		// Check for image optimization plugins.
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php'     => 'Yoast SEO',
			'seo-by-rank-math/rank-math.php' => 'Rank Math SEO',
		);

		$seo_plugin_detected = false;
		foreach ( $seo_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$seo_plugin_detected = true;
				break;
			}
		}

		// Critical: Poor image SEO across multiple factors.
		if ( $alt_percentage < 50 && $bad_filename_percentage > 40 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: alt percentage, 2: bad filename percentage */
					__( 'Image SEO optimization not implemented. Only %1$d%% of images have alt text, and %2$d%% have generic filenames (IMG_1234.jpg). Google Images can\'t index images without descriptive filenames and alt text. Rename files before upload ("red-bicycle.jpg" not "DSC_0123.jpg") and add alt text in Media Library.', 'wpshadow' ),
					$alt_percentage,
					$bad_filename_percentage
				),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/image-seo?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'total_images'            => $total_images,
					'images_with_alt'         => $images_with_alt,
					'images_with_title'       => $images_with_title,
					'images_with_caption'     => $images_with_caption,
					'images_with_bad_filename' => $images_with_bad_filename,
					'alt_percentage'          => $alt_percentage,
					'bad_filename_percentage' => $bad_filename_percentage,
					'recommendation'          => __( 'Image SEO checklist: 1) Rename files BEFORE upload ("product-name.jpg" not "IMG_1234.jpg"). 2) Add descriptive alt text in Media Library. 3) Fill in Title and Caption fields. 4) Use descriptive folder structure. 5) Consider image sitemap for large galleries.', 'wpshadow' ),
					'filename_best_practices' => array(
						'good' => 'red-bicycle-women-mountain.jpg',
						'bad' => 'DSC_1234.jpg, IMG_0001.jpg, Screenshot_20260204.png',
						'format' => 'descriptive-with-hyphens-lowercase.jpg',
						'rename_before_upload' => 'Easier than renaming in Media Library',
					),
					'seo_impact'              => array(
						'google_images' => 'Filenames and alt text are ranking factors',
						'accessibility' => 'Screen readers need alt text',
						'image_search' => 'Can drive 10-30% of organic traffic',
					),
				),
			);
		}

		// No issues - good image SEO practices.
		return null;
	}
}
