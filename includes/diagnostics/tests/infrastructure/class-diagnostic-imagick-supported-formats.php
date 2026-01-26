<?php
/**
 * Diagnostic: Imagick Supported Formats
 *
 * Lists image formats supported by Imagick installation.
 * Missing formats can prevent WordPress from processing certain image types.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Infrastructure
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Imagick_Supported_Formats
 *
 * Checks which image formats Imagick can process.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Imagick_Supported_Formats extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'imagick-supported-formats';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Imagick Supported Formats';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Lists image formats supported by Imagick';

	/**
	 * Check Imagick supported formats.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if Imagick is available.
		if ( ! extension_loaded( 'imagick' ) ) {
			return null; // Not applicable if Imagick not installed.
		}

		// Get supported formats.
		$imagick = new \Imagick();
		$formats = $imagick->queryFormats();

		// Essential formats for WordPress.
		$essential_formats = array( 'JPEG', 'PNG', 'GIF' );
		$recommended_formats = array( 'WEBP', 'AVIF', 'SVG', 'TIFF', 'BMP' );

		$missing_essential = array();
		$missing_recommended = array();

		// Check essential formats.
		foreach ( $essential_formats as $format ) {
			if ( ! in_array( $format, $formats, true ) ) {
				$missing_essential[] = $format;
			}
		}

		// Check recommended formats.
		foreach ( $recommended_formats as $format ) {
			if ( ! in_array( $format, $formats, true ) ) {
				$missing_recommended[] = $format;
			}
		}

		// Critical: Missing essential formats.
		if ( ! empty( $missing_essential ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Comma-separated list of missing formats */
					__( 'Imagick is missing essential format support: %s. WordPress may not be able to process these images.', 'wpshadow' ),
					implode( ', ', $missing_essential )
				),
				'severity'    => 'high',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/imagick_supported_formats',
				'meta'        => array(
					'missing_essential'   => $missing_essential,
					'missing_recommended' => $missing_recommended,
					'total_formats'       => count( $formats ),
				),
			);
		}

		// Informational: Missing recommended formats.
		if ( ! empty( $missing_recommended ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Comma-separated list of missing formats */
					__( 'Imagick is missing support for recommended formats: %s. Consider updating Imagick for better format support.', 'wpshadow' ),
					implode( ', ', $missing_recommended )
				),
				'severity'    => 'info',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/imagick_supported_formats',
				'meta'        => array(
					'missing_recommended' => $missing_recommended,
					'total_formats'       => count( $formats ),
				),
			);
		}

		// All important formats are supported.
		return null;
	}
}
