<?php
/**
 * Crop vs Resize Settings Diagnostic
 *
 * Validates image crop/resize configuration for balance between quality and storage.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Crop vs Resize Settings Diagnostic Class
 *
 * Reviews WordPress image size crop settings for potential quality or storage issues.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Crop_Vs_Resize_Settings extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'crop-vs-resize-settings';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Crop vs Resize Settings';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Reviews hard crop vs proportional resize settings for image sizes.';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$thumbnail_crop = (bool) get_option( 'thumbnail_crop', 1 );
		$thumb_w = (int) get_option( 'thumbnail_size_w', 150 );
		$thumb_h = (int) get_option( 'thumbnail_size_h', 150 );

		if ( ! $thumbnail_crop ) {
			return null;
		}

		if ( $thumb_w === 0 || $thumb_h === 0 ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Thumbnail images are hard-cropped. If key parts of images are being cut off, consider switching to proportional resizing for better visual quality.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/crop-vs-resize-settings',
		);
	}
}
