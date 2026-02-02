<?php
/**
 * Media Gutenberg Block Integration Diagnostic
 *
 * Checks if media blocks in Gutenberg editor work properly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Gutenberg Block Integration Diagnostic Class
 *
 * Verifies that Gutenberg media blocks (Image, Gallery, Video, Audio)
 * are properly registered and functional with media library integration.
 *
 * @since 1.26033.0000
 */
class Diagnostic_Media_Gutenberg_Block_Integration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-gutenberg-block-integration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Gutenberg Block Integration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if media blocks in Gutenberg editor work properly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if Gutenberg/Block Editor is available.
		if ( ! function_exists( 'register_block_type' ) ) {
			$issues[] = __( 'Block Editor (Gutenberg) is not available', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-gutenberg-block-integration',
			);
		}

		// Check if block editor is disabled.
		if ( ! use_block_editor_for_post_type( 'post' ) ) {
			$issues[] = __( 'Block Editor is disabled for posts', 'wpshadow' );
		}

		// Check if core media blocks are registered.
		$registry = \WP_Block_Type_Registry::get_instance();
		$media_blocks = array(
			'core/image'   => 'Image block',
			'core/gallery' => 'Gallery block',
			'core/video'   => 'Video block',
			'core/audio'   => 'Audio block',
			'core/file'    => 'File block',
		);

		foreach ( $media_blocks as $block_name => $block_label ) {
			if ( ! $registry->is_registered( $block_name ) ) {
				$issues[] = sprintf(
					/* translators: %s: block name */
					__( '%s is not registered', 'wpshadow' ),
					$block_label
				);
			}
		}

		// Check if block editor scripts are enqueued.
		if ( ! wp_script_is( 'wp-block-library', 'registered' ) ) {
			$issues[] = __( 'Block library script is not registered', 'wpshadow' );
		}

		// Check if media modal integration exists.
		if ( ! wp_script_is( 'media-editor', 'registered' ) ) {
			$issues[] = __( 'Media editor integration script is not registered', 'wpshadow' );
		}

		// Check for block editor assets.
		if ( ! wp_script_is( 'wp-editor', 'registered' ) ) {
			$issues[] = __( 'Block editor script is not registered', 'wpshadow' );
		}

		// Check if REST API is available for media.
		$rest_available = rest_get_server();
		if ( empty( $rest_available ) ) {
			$issues[] = __( 'REST API is not available (required for Block Editor)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-gutenberg-block-integration',
			);
		}

		return null;
	}
}
