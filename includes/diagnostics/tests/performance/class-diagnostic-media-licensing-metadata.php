<?php
/**
 * Media Licensing Metadata Diagnostic
 *
 * Tests copyright and licensing metadata storage.
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
 * Media Licensing Metadata Diagnostic Class
 *
 * Verifies storage and retrieval of copyright and licensing metadata for media.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Media_Licensing_Metadata extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-licensing-metadata';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Licensing Metadata';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests copyright and licensing metadata storage';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for licensing plugins.
		$licensing_plugins = array(
			'copyright-and-license-manager/copyright-license-manager.php',
			'media-licensing/media-licensing.php',
		);

		$has_licensing = false;
		foreach ( $licensing_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_licensing = true;
				break;
			}
		}

		if ( ! $has_licensing ) {
			$issues[] = __( 'No media licensing plugin detected', 'wpshadow' );
		}

		// Check if attachment metadata includes license fields.
		$attachments = get_posts( array(
			'post_type'      => 'attachment',
			'posts_per_page' => 5,
		) );

		if ( ! empty( $attachments ) ) {
			$license_fields = array(
				'_wpshadow_license',
				'_wpshadow_copyright',
				'_wpshadow_attribution',
			);

			$missing_license = 0;
			foreach ( $attachments as $attachment ) {
				$has_license = false;
				foreach ( $license_fields as $field ) {
					$value = get_post_meta( $attachment->ID, $field, true );
					if ( ! empty( $value ) ) {
						$has_license = true;
						break;
					}
				}
				if ( ! $has_license ) {
					$missing_license++;
				}
			}

			if ( $missing_license === count( $attachments ) ) {
				$issues[] = __( 'No licensing metadata found on attachments', 'wpshadow' );
			}
		}

		// Check for EXIF copyright data reading.
		if ( ! function_exists( 'wp_read_image_metadata' ) ) {
			$issues[] = __( 'Image metadata reading function not available', 'wpshadow' );
		}

		// Check if license fields are visible in media editor.
		$has_media_columns = has_filter( 'manage_media_columns' );
		if ( ! $has_media_columns ) {
			$issues[] = __( 'No custom media columns registered for license display', 'wpshadow' );
		}

		// Check for license validation on upload.
		$has_upload_validation = has_filter( 'wp_handle_upload' );
		if ( ! $has_upload_validation ) {
			$issues[] = __( 'No upload validation for licensing requirements', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-licensing-metadata',
			);
		}

		return null;
	}
}
