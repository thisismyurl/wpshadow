<?php
/**
 * Treatment: Set Default Image Link to None
 *
 * Updates the image_default_link_type option to 'none', so newly inserted
 * images do not automatically link to the attachment page or file URL. This
 * is the correct default for virtually all modern sites. The previous value
 * is stored so the setting can be restored via undo().
 *
 * Risk level: safe — single option update, fully reversible.
 *
 * @package ThisIsMyURL\Shadow
 * @since   0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Treatments;

use ThisIsMyURL\Shadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets the default image link type to none.
 */
class Treatment_Image_Link_Default extends Treatment_Base {

	/**
	 * @var string
	 */
	protected static $slug = 'image-link-default';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Set image_default_link_type to 'none'.
	 *
	 * @return array
	 */
	public static function apply() {
		$previous = get_option( 'image_default_link_type', 'none' );

		// Store for undo.
		update_option( 'thisismyurl_shadow_prev_image_default_link_type', $previous, false );

		update_option( 'image_default_link_type', 'none' );

		return array(
			'success' => true,
			'message' => __( 'Default image link type set to "none". New images inserted via the block editor or classic editor will no longer auto-link to the attachment page or file URL.', 'thisismyurl-shadow' ),
			'details' => array(
				'previous_value' => $previous,
				'new_value'      => 'none',
			),
		);
	}

	/**
	 * Restore the previous image_default_link_type value.
	 *
	 * @return array
	 */
	public static function undo() {
		$previous = get_option( 'thisismyurl_shadow_prev_image_default_link_type' );

		if ( false === $previous ) {
			return array(
				'success' => false,
				'message' => __( 'No previous value stored — nothing to restore.', 'thisismyurl-shadow' ),
			);
		}

		update_option( 'image_default_link_type', $previous );
		delete_option( 'thisismyurl_shadow_prev_image_default_link_type' );

		return array(
			'success' => true,
			'message' => sprintf(
						/* translators: %s: restored option value. */
				__( 'Default image link type restored to "%s".', 'thisismyurl-shadow' ),
				$previous
			),
		);
	}
}
