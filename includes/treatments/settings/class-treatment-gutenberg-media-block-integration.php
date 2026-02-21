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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Gutenberg_Media_Block_Integration' );
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
