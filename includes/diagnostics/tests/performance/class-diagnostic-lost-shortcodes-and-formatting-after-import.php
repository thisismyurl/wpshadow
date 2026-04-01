<?php
/**
 * Lost Shortcodes and Formatting After Import Diagnostic
 *
 * Tests whether shortcodes and formatting survive import.
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
 * Lost Shortcodes and Formatting After Import Diagnostic Class
 *
 * Tests whether page builder shortcodes, Gutenberg blocks, and formatting survive import.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Lost_Shortcodes_And_Formatting_After_Import extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'lost-shortcodes-and-formatting-after-import';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Lost Shortcodes and Formatting After Import';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether shortcodes and formatting survive import';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for page builder plugins.
		$page_builders = array(
			'elementor/elementor.php',
			'divi/divi.php',
			'beaver-builder/fl-builder.php',
			'brizy/brizy.php',
		);

		$has_builder = false;
		foreach ( $page_builders as $builder ) {
			if ( is_plugin_active( $builder ) ) {
				$has_builder = true;
				break;
			}
		}

		// Sample posts with shortcodes.
		$posts_with_shortcodes = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'posts_per_page' => 20,
			'orderby'        => 'modified',
			'order'          => 'DESC',
			's'              => '[',  // Posts containing shortcodes.
		) );

		if ( ! empty( $posts_with_shortcodes ) ) {
			// We found posts with brackets - check if shortcodes are registered.
			$broken_shortcodes = 0;

			foreach ( $posts_with_shortcodes as $post ) {
				// Extract shortcode names.
				if ( preg_match_all( '/\[(\w+)[\s\]]/', $post->post_content, $matches ) ) {
					foreach ( array_unique( $matches[1] ) as $shortcode_name ) {
						// Check if shortcode exists.
						if ( ! shortcode_exists( $shortcode_name ) ) {
							$broken_shortcodes++;
							break; // Only count once per post.
						}
					}
				}
			}

			if ( $broken_shortcodes > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of posts with unregistered shortcodes */
					__( '%d posts contain unregistered shortcodes', 'wpshadow' ),
					$broken_shortcodes
				);
			}
		}

		// Check for Gutenberg blocks.
		$gutenberg_posts = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'posts_per_page' => 10,
			'orderby'        => 'modified',
			'order'          => 'DESC',
			's'              => '<!-- wp:',  // Gutenberg block comments.
		) );

		if ( ! empty( $gutenberg_posts ) ) {
			// Check if Gutenberg block types are registered.
			$block_registry = \WP_Block_Type_Registry::get_instance();
			$missing_blocks = 0;

			foreach ( $gutenberg_posts as $post ) {
				if ( preg_match_all( '/<!-- wp:(\S+)[\s-]/', $post->post_content, $matches ) ) {
					foreach ( array_unique( $matches[1] ) as $block_name ) {
						if ( ! $block_registry->is_registered( $block_name ) ) {
							$missing_blocks++;
							break; // Only count once per post.
						}
					}
				}
			}

			if ( $missing_blocks > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of posts with unregistered blocks */
					__( '%d posts contain unregistered Gutenberg blocks', 'wpshadow' ),
					$missing_blocks
				);
			}
		}

		// Check for post meta serialization issues (page builder data).
		if ( $has_builder ) {
			$posts_with_meta = get_posts( array(
				'meta_query' => array(
					array(
						'key'     => '_elementor_data',
						'compare' => 'EXISTS',
					),
				),
			) );

			if ( ! empty( $posts_with_meta ) ) {
				$corrupted_meta = 0;
				foreach ( $posts_with_meta as $post ) {
					$meta_value = get_post_meta( $post->ID, '_elementor_data', true );
					if ( ! is_array( json_decode( $meta_value, true ) ) ) {
						$corrupted_meta++;
					}
				}

				if ( $corrupted_meta > 0 ) {
					$issues[] = sprintf(
						/* translators: %d: number of posts with corrupted page builder data */
						__( '%d posts have corrupted page builder metadata', 'wpshadow' ),
						$corrupted_meta
					);
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/lost-shortcodes-and-formatting-after-import?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
