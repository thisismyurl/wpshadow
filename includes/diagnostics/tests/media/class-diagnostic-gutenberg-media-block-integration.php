<?php
/**
 * Gutenberg Media Block Integration Diagnostic
 *
 * Tests media block functionality in Gutenberg.
 * Validates image/video/gallery block rendering.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7033.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gutenberg Media Block Integration Diagnostic Class
 *
 * Checks if Gutenberg media blocks are properly integrated
 * and rendering correctly.
 *
 * @since 1.7033.1200
 */
class Diagnostic_Gutenberg_Media_Block_Integration extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gutenberg-media-block-integration';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Gutenberg Media Block Integration';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media block functionality in Gutenberg';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests if Gutenberg media blocks are functioning and
	 * rendering properly on the frontend.
	 *
	 * @since  1.7033.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wp_version;

		// Check if Gutenberg/Block Editor is available (WordPress 5.0+).
		$has_block_editor = version_compare( $wp_version, '5.0', '>=' );

		if ( ! $has_block_editor ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WordPress version is too old to support Gutenberg block editor', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/gutenberg-media-block-integration',
				'details'      => array(
					'wp_version'        => $wp_version,
					'required_version'  => '5.0',
					'recommendation'    => __( 'Update WordPress to 5.0 or later for block editor support', 'wpshadow' ),
				),
			);
		}

		// Check if block editor is disabled.
		$classic_editor_active = is_plugin_active( 'classic-editor/classic-editor.php' );
		$disable_gutenberg_active = is_plugin_active( 'disable-gutenberg/disable-gutenberg.php' );

		if ( $classic_editor_active || $disable_gutenberg_active ) {
			return null; // Block editor intentionally disabled.
		}

		// Check for block registration.
		$registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();

		$media_blocks = array(
			'core/image',
			'core/video',
			'core/audio',
			'core/gallery',
			'core/media-text',
			'core/cover',
		);

		$missing_blocks = array();
		foreach ( $media_blocks as $block ) {
			if ( ! isset( $registered_blocks[ $block ] ) ) {
				$missing_blocks[] = $block;
			}
		}

		// Check for posts with Gutenberg blocks.
		$posts_with_blocks = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 5,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$block_posts_count = 0;
		$image_blocks_count = 0;
		$test_post = null;

		foreach ( $posts_with_blocks as $post ) {
			if ( has_blocks( $post->post_content ) ) {
				$block_posts_count++;

				// Check for image blocks.
				if ( false !== strpos( $post->post_content, '<!-- wp:image' ) ) {
					$image_blocks_count++;
					if ( null === $test_post ) {
						$test_post = $post;
					}
				}
			}
		}

		// Test block rendering if we have a test post.
		$rendering_issues = array();

		if ( $test_post ) {
			$post_url = get_permalink( $test_post->ID );
			$response = wp_remote_get(
				$post_url,
				array(
					'timeout'    => 10,
					'user-agent' => 'WPShadow/1.0 (Block Integration Diagnostic)',
				)
			);

			if ( ! is_wp_error( $response ) ) {
				$html = wp_remote_retrieve_body( $response );

				// Check for block wrapper classes.
				$has_block_classes = false !== strpos( $html, 'wp-block-image' );

				// Check for image blocks.
				preg_match_all( '/<figure[^>]*class=["\'][^"\']*wp-block-image[^"\']*["\'][^>]*>/i', $html, $figure_matches );
				$figure_count = count( $figure_matches[0] );

				if ( ! $has_block_classes || 0 === $figure_count ) {
					$rendering_issues[] = 'block_styles_missing';
				}

				// Check for block editor styles enqueued.
				if ( false === strpos( $html, 'wp-block-library' ) ) {
					$rendering_issues[] = 'block_styles_not_enqueued';
				}
			}
		}

		// Check theme support.
		$theme_supports_blocks = current_theme_supports( 'align-wide' );
		$theme_supports_responsive = current_theme_supports( 'responsive-embeds' );

		// Check block editor assets.
		global $wp_styles, $wp_scripts;

		$block_styles_registered = isset( $wp_styles->registered['wp-block-library'] );
		$block_scripts_registered = isset( $wp_scripts->registered['wp-block-library'] );

		// Issue: Missing blocks or rendering problems.
		if ( ! empty( $missing_blocks ) || ! empty( $rendering_issues ) || ! $block_styles_registered ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Gutenberg media blocks may not be functioning properly or are missing', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/gutenberg-media-block-integration',
				'details'      => array(
					'wp_version'               => $wp_version,
					'has_block_editor'         => $has_block_editor,
					'classic_editor_active'    => $classic_editor_active,
					'missing_blocks'           => $missing_blocks,
					'expected_blocks'          => $media_blocks,
					'block_posts_count'        => $block_posts_count,
					'image_blocks_count'       => $image_blocks_count,
					'rendering_issues'         => $rendering_issues,
					'theme_supports_blocks'    => $theme_supports_blocks,
					'theme_supports_responsive' => $theme_supports_responsive,
					'block_styles_registered'  => $block_styles_registered,
					'block_scripts_registered' => $block_scripts_registered,
					'tested_post'              => $test_post ? array(
						'id'    => $test_post->ID,
						'title' => get_the_title( $test_post->ID ),
						'url'   => get_permalink( $test_post->ID ),
					) : null,
					'issue_types'              => array(
						'block_styles_missing'       => __( 'Block wrapper classes not found in HTML', 'wpshadow' ),
						'block_styles_not_enqueued'  => __( 'wp-block-library styles not loaded', 'wpshadow' ),
					),
					'recommendation'           => __( 'Ensure theme supports Gutenberg blocks and block styles are properly enqueued', 'wpshadow' ),
					'theme_support_code'       => "add_theme_support( 'align-wide' );\nadd_theme_support( 'responsive-embeds' );\nadd_theme_support( 'wp-block-styles' );",
					'enqueue_code'             => "add_action( 'wp_enqueue_scripts', function() {\n    wp_enqueue_style( 'wp-block-library' );\n} );",
				),
			);
		}

		return null;
	}
}
