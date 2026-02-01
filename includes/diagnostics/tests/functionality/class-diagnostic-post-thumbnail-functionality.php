<?php
/**
 * Post Thumbnail Functionality Diagnostic
 *
 * Validates that post thumbnails (featured images) are properly configured
 * and used consistently throughout the theme.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1330
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Thumbnail Functionality Diagnostic Class
 *
 * Checks post thumbnail configuration and usage.
 *
 * @since 1.6032.1330
 */
class Diagnostic_Post_Thumbnail_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-thumbnail-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Thumbnail Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates post thumbnail configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1330
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if theme supports post thumbnails.
		if ( ! current_theme_supports( 'post-thumbnails' ) ) {
			$issues[] = __( 'Theme does not support post thumbnails', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme does not support post thumbnails (featured images).', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'recommendation' => __( 'Add post thumbnail support to theme with add_theme_support( "post-thumbnails" ).', 'wpshadow' ),
				),
			);
		}

		// Check for custom thumbnail sizes.
		global $_wp_additional_image_sizes;
		$custom_sizes = $_wp_additional_image_sizes;

		if ( empty( $custom_sizes ) ) {
			$issues[] = __( 'No custom thumbnail sizes defined (using WordPress defaults only)', 'wpshadow' );
		}

		// Check template usage.
		$template_dir = get_template_directory();
		$templates    = array( 'single.php', 'archive.php', 'index.php', 'home.php' );

		$templates_using_thumbnails = array();
		foreach ( $templates as $template ) {
			$file = $template_dir . '/' . $template;
			if ( file_exists( $file ) ) {
				$content = file_get_contents( $file );
				if ( false !== stripos( $content, 'the_post_thumbnail' ) || false !== stripos( $content, 'get_the_post_thumbnail' ) ) {
					$templates_using_thumbnails[] = $template;
				}
			}
		}

		if ( empty( $templates_using_thumbnails ) ) {
			$issues[] = __( 'Theme templates do not display post thumbnails', 'wpshadow' );
		}

		// Check posts without thumbnails.
		global $wpdb;
		$total_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_type = 'post'
			AND post_status = 'publish'"
		);

		$posts_with_thumbnails = $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.ID)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_type = 'post'
			AND p.post_status = 'publish'
			AND pm.meta_key = '_thumbnail_id'
			AND pm.meta_value != ''"
		);

		$posts_without_thumbnails = $total_posts - $posts_with_thumbnails;
		$percentage_without       = $total_posts > 0 ? ( $posts_without_thumbnails / $total_posts ) * 100 : 0;

		if ( $percentage_without > 50 && $total_posts > 10 ) {
			$issues[] = sprintf(
				/* translators: 1: posts without thumbnails, 2: total posts, 3: percentage */
				__( '%1$d of %2$d posts (%.0f%%) lack featured images', 'wpshadow' ),
				$posts_without_thumbnails,
				$total_posts,
				$percentage_without
			);
		}

		// Check for broken thumbnail references.
		$broken_thumbnails = $wpdb->get_results(
			"SELECT p.ID, p.post_title, pm.meta_value as thumbnail_id
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_type = 'post'
			AND p.post_status = 'publish'
			AND pm.meta_key = '_thumbnail_id'
			AND pm.meta_value != ''
			AND pm.meta_value NOT IN (
				SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment'
			)"
		);

		if ( ! empty( $broken_thumbnails ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of broken thumbnails */
				__( '%d posts reference non-existent featured images', 'wpshadow' ),
				count( $broken_thumbnails )
			);
		}

		// Check thumbnail image sizes.
		if ( ! empty( $posts_with_thumbnails ) ) {
			$large_thumbnails = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
				INNER JOIN {$wpdb->posts} p ON pm.meta_value = p.ID
				INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id
				WHERE pm.meta_key = '_thumbnail_id'
				AND pm2.meta_key = '_wp_attached_file'
				AND p.post_type = 'attachment'"
			);

			// Can't easily check file sizes without reading files, but we can warn.
		}

		// Check for default placeholder image.
		$functions_file = $template_dir . '/functions.php';
		if ( file_exists( $functions_file ) ) {
			$content = file_get_contents( $functions_file );
			if ( false === stripos( $content, 'default-thumbnail' ) && false === stripos( $content, 'placeholder' ) ) {
				$issues[] = __( 'Theme lacks default placeholder for posts without thumbnails', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of thumbnail issues */
					__( 'Found %d post thumbnail configuration issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'details'      => array(
					'issues'                   => $issues,
					'total_posts'              => $total_posts,
					'posts_with_thumbnails'    => $posts_with_thumbnails,
					'posts_without_thumbnails' => $posts_without_thumbnails,
					'broken_thumbnails'        => count( $broken_thumbnails ),
					'recommendation'           => __( 'Add featured images to posts, define custom thumbnail sizes, and include default placeholder image.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
