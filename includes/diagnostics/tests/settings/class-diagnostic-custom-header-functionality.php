<?php
/**
 * Custom Header Functionality Diagnostic
 *
 * Validates that custom header support is properly configured
 * in the theme with appropriate dimensions and settings.
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
 * Custom Header Functionality Diagnostic Class
 *
 * Checks custom header configuration.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Custom_Header_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-header-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Header Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates custom header configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if theme supports custom header.
		$supports = current_theme_supports( 'custom-header' );

		if ( ! $supports ) {
			return null; // No custom header support - not an issue if not needed.
		}

		// Get custom header support arguments.
		$support_args = get_theme_support( 'custom-header' );

		if ( is_array( $support_args ) && isset( $support_args[0] ) ) {
			$args = $support_args[0];

			// Check for reasonable dimensions.
			if ( isset( $args['width'] ) && $args['width'] < 100 ) {
				$issues[] = __( 'Custom header width is too small (< 100px)', 'wpshadow' );
			}

			if ( isset( $args['height'] ) && $args['height'] < 50 ) {
				$issues[] = __( 'Custom header height is too small (< 50px)', 'wpshadow' );
			}

			// Check for excessively large dimensions.
			if ( isset( $args['width'] ) && $args['width'] > 3000 ) {
				$issues[] = __( 'Custom header width is excessive (> 3000px)', 'wpshadow' );
			}

			if ( isset( $args['height'] ) && $args['height'] > 1500 ) {
				$issues[] = __( 'Custom header height is excessive (> 1500px)', 'wpshadow' );
			}

			// Check for proper callbacks.
			if ( isset( $args['wp-head-callback'] ) && ! is_callable( $args['wp-head-callback'] ) ) {
				$issues[] = __( 'wp-head-callback is not callable', 'wpshadow' );
			}

			if ( isset( $args['admin-head-callback'] ) && ! is_callable( $args['admin-head-callback'] ) ) {
				$issues[] = __( 'admin-head-callback is not callable', 'wpshadow' );
			}

			if ( isset( $args['admin-preview-callback'] ) && ! is_callable( $args['admin-preview-callback'] ) ) {
				$issues[] = __( 'admin-preview-callback is not callable', 'wpshadow' );
			}
		}

		// Check current header settings.
		$header_image = get_header_image();
		$header_text  = display_header_text();

		// Check if header image exists.
		if ( ! empty( $header_image ) && false === strpos( $header_image, '%' ) ) {
			$image_path = str_replace( content_url(), WP_CONTENT_DIR, $header_image );
			if ( ! file_exists( $image_path ) ) {
				$issues[] = __( 'Custom header image file is missing', 'wpshadow' );
			}
		}

		// Check template for header display.
		$template_dir = get_template_directory();
		$header_php   = $template_dir . '/header.php';

		if ( file_exists( $header_php ) ) {
			$content = file_get_contents( $header_php );

			// Check if theme actually displays custom header.
			if ( false === stripos( $content, 'get_header_image' ) && false === stripos( $content, 'the_custom_header_markup' ) ) {
				$issues[] = __( 'Theme declares custom header support but header.php does not display it', 'wpshadow' );
			}

			// Check for header text display.
			if ( false === stripos( $content, 'display_header_text' ) && false === stripos( $content, 'get_header_textcolor' ) ) {
				// Not critical, but worth noting.
			}
		} else {
			$issues[] = __( 'Theme is missing header.php template', 'wpshadow' );
		}

		// Check for multiple custom headers (video, random).
		$has_default_headers = false;
		if ( is_array( $support_args ) && isset( $support_args[0]['default-image'] ) ) {
			$has_default_headers = true;
		}

		// Check for header video support.
		if ( current_theme_supports( 'custom-header', 'video' ) ) {
			$header_video = get_header_video_url();
			if ( ! empty( $header_video ) ) {
				// Check if video is from external source or local.
				if ( false === strpos( $header_video, 'youtube.com' ) && false === strpos( $header_video, 'vimeo.com' ) ) {
					// Local video - check file size concerns.
					$video_path = str_replace( content_url(), WP_CONTENT_DIR, $header_video );
					if ( file_exists( $video_path ) ) {
						$video_size = filesize( $video_path );
						if ( $video_size > 10 * 1024 * 1024 ) { // 10MB.
							$issues[] = sprintf(
								/* translators: %s: video file size */
								__( 'Header video is large (%s) - may impact performance', 'wpshadow' ),
								size_format( $video_size )
							);
						}
					}
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of custom header issues */
					__( 'Found %d custom header configuration issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'details'      => array(
					'issues'       => $issues,
					'header_image' => $header_image,
					'header_text'  => $header_text,
					'recommendation' => __( 'Ensure custom header callbacks are callable and header images exist. Display header in header.php template.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
