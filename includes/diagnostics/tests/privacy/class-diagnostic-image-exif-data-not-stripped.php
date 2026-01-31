<?php
/**
 * Image EXIF Data Not Stripped Diagnostic
 *
 * Checks if image EXIF data is removed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2315
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image EXIF Data Not Stripped Diagnostic Class
 *
 * Detects unstripped image EXIF data.
 *
 * @since 1.2601.2315
 */
class Diagnostic_Image_EXIF_Data_Not_Stripped extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-exif-data-not-stripped';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image EXIF Data Not Stripped';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if image EXIF data is removed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2315
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for EXIF stripping plugins
		$exif_plugins = array(
			'wp-image-metadata-remover/wp-image-metadata-remover.php',
			'imagify/imagify.php',
		);

		$exif_active = false;
		foreach ( $exif_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$exif_active = true;
				break;
			}
		}

		if ( ! $exif_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Image EXIF data is not being stripped. This data can contain location information and camera details, compromising user privacy.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/image-exif-data-not-stripped',
			);
		}

		return null;
	}
}
