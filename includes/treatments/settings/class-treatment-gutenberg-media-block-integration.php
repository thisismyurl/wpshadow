<?php
/**
 * Gutenberg Media Block Integration Treatment
 *
 * Detects if Gutenberg block editor media blocks are properly integrated.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.1635
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Gutenberg_Media_Block_Integration Class
 *
 * Tests if Gutenberg media blocks (image, video, audio, file) are properly
 * registered and integrated with the block editor for optimal media management.
 *
 * @since 1.6033.1635
 */
class Treatment_Gutenberg_Media_Block_Integration extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'gutenberg-media-block-integration';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Gutenberg Media Block Integration';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies Gutenberg media blocks are properly integrated';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6033.1635
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if Gutenberg is available
		global $wp_version;
		$has_gutenberg = version_compare( $wp_version, '5.0', '>=' ) || 
			( function_exists( 'gutenberg_is_experiment_enabled' ) );

		if ( ! $has_gutenberg ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Gutenberg block editor is not available. Update WordPress to 5.0 or newer.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/gutenberg-media-blocks',
			);
		}

		// Check for media blocks registration
		$media_blocks = self::check_media_blocks();
		if ( empty( $media_blocks ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Media blocks are not properly registered in Gutenberg. Verify block editor configuration.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/gutenberg-media-blocks',
			);
		}

		return null;
	}

	/**
	 * Check if media blocks are registered
	 *
	 * @since  1.6033.1635
	 * @return array List of registered media blocks.
	 */
	private static function check_media_blocks() {
		$required_blocks = array( 'core/image', 'core/video', 'core/audio', 'core/file' );
		$registered      = array();

		if ( function_exists( 'get_block_type' ) ) {
			foreach ( $required_blocks as $block ) {
				if ( get_block_type( $block ) ) {
					$registered[] = $block;
				}
			}
		}

		return $registered;
	}
}
