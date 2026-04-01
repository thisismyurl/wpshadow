<?php
/**
 * Image Color Profile Diagnostic
 *
 * Detects CMYK images and color profile optimization opportunities.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Color Profile Diagnostic
 *
 * Identifies images with non-web color profiles that need conversion.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Image_Color_Profile extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-color-profile';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Color Profile';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects CMYK images and color profile optimization opportunities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if ImageMagick is available (required for color profile detection)
		if ( ! extension_loaded( 'imagick' ) ) {
			return null;
		}

		global $wpdb;

		// Get recent image uploads (last 100)
		$images = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_mime_type FROM {$wpdb->posts}
				WHERE post_type = 'attachment'
				AND post_mime_type LIKE %s
				ORDER BY post_date DESC
				LIMIT 100",
				'image/%'
			)
		);

		if ( empty( $images ) ) {
			return null;
		}

		$cmyk_count      = 0;
		$profile_count   = 0;
		$problematic_images = array();

		foreach ( $images as $image ) {
			$file_path = get_attached_file( $image->ID );
			if ( ! file_exists( $file_path ) ) {
				continue;
			}

			try {
				$imagick = new \Imagick( $file_path );
				$colorspace = $imagick->getImageColorspace();

				// Check for CMYK colorspace (not web-friendly)
				if ( $colorspace === \Imagick::COLORSPACE_CMYK ) {
					$cmyk_count++;
					$problematic_images[] = array(
						'id'         => $image->ID,
						'colorspace' => 'CMYK',
						'size'       => filesize( $file_path ),
					);
				}

				// Check for embedded color profiles
				$profiles = $imagick->getImageProfiles( 'icc', true );
				if ( ! empty( $profiles ) ) {
					$profile_count++;
				}

				$imagick->clear();
				$imagick->destroy();
			} catch ( \Exception $e ) {
				// Skip problematic images
				continue;
			}
		}

		// Generate findings if CMYK images detected
		if ( $cmyk_count > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of CMYK images, 2: total checked */
					__( '%1$d CMYK images detected (of %2$d checked). Convert to RGB/sRGB for web compatibility.', 'wpshadow' ),
					$cmyk_count,
					count( $images )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/image-color-profile?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'cmyk_count'       => $cmyk_count,
					'total_checked'    => count( $images ),
					'problematic_images' => $problematic_images,
					'recommendation'   => 'Convert CMYK to sRGB using ImageMagick or optimization plugins',
					'impact_estimate'  => '15-25% file size reduction + correct colors',
					'browser_support'  => 'CMYK causes color shifts in most browsers',
					'solution'         => 'Use EWWW Image Optimizer or ShortPixel with auto-conversion',
				),
			);
		}

		// Check for excessive embedded profiles
		if ( $profile_count > 20 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of images with profiles */
					__( '%d images contain embedded color profiles. Stripping profiles reduces file size.', 'wpshadow' ),
					$profile_count
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/image-color-profile?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'profile_count'    => $profile_count,
					'total_checked'    => count( $images ),
					'recommendation'   => 'Strip color profiles during optimization',
					'impact_estimate'  => '5-15 KB per image savings',
					'note'             => 'Convert to sRGB first, then strip profile',
				),
			);
		}

		return null;
	}
}
