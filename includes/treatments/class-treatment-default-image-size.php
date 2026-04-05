<?php
/**
 * Treatment: Set default image insertion size to "large"
 *
 * When editors insert images from the Media Library, WordPress uses the
 * image_default_size option to pre-select the insertion size. An empty value
 * or "full" causes full-resolution images to appear by default, which hurts
 * page performance. This treatment sets the default to "large" so editors
 * get a sensibly-sized image unless they consciously override it.
 *
 * Undo: restores the previous value.
 *
 * @package WPShadow
 * @since   0.6093.1900
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets the default image insertion size to "large".
 */
class Treatment_Default_Image_Size extends Treatment_Base {

	/** @var string */
	protected static $slug = 'default-image-size';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Set image_default_size to 'large'.
	 *
	 * @return array
	 */
	public static function apply(): array {
		return static::apply_option_with_backup(
			'image_default_size',
			'large',
			'wpshadow_default_image_size_prev',
			__( 'Default image insertion size is already set to "large". No changes made.', 'wpshadow' ),
			__( 'Default image insertion size set to "large". New image inserts will default to the large size instead of full resolution.', 'wpshadow' )
		);
	}

	/**
	 * Restore the previous image_default_size value.
	 *
	 * @return array
	 */
	public static function undo(): array {
		return static::restore_option_from_backup(
			'image_default_size',
			'wpshadow_default_image_size_prev',
			__( 'No previous image size setting found to restore.', 'wpshadow' ),
			static function ( $prev ): string {
				return sprintf(
					/* translators: %s: Restored size name */
					__( 'Default image insertion size restored to "%s".', 'wpshadow' ),
					esc_html( (string) $prev )
				);
			}
		);
	}
}
