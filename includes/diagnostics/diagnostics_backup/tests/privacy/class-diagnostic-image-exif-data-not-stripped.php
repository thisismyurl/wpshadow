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
		$issues = array();

		// Check 1: Verify EXIF stripping enabled
		$exif_stripping = get_option( 'wpshadow_exif_stripping_enabled', false );
		if ( ! $exif_stripping ) {
			$issues[] = __( 'Image EXIF stripping not enabled in settings', 'wpshadow' );
		}

		// Check 2: Check dedicated EXIF plugin active
		$exif_plugin = is_plugin_active( 'wp-image-metadata-remover/wp-image-metadata-remover.php' );
		if ( ! $exif_plugin ) {
			$issues[] = __( 'No EXIF metadata removal plugin active', 'wpshadow' );
		}

		// Check 3: Verify image optimization has EXIF removal
		$imagify_exif = is_plugin_active( 'imagify/imagify.php' );
		$imagify_removal = get_option( 'imagify_remove_exif_data', false );
		if ( $imagify_exif && ! $imagify_removal ) {
			$issues[] = __( 'Imagify EXIF removal not enabled', 'wpshadow' );
		}

		// Check 4: Check SSL for media uploads
		if ( ! is_ssl() ) {
			$issues[] = __( 'HTTPS not enabled for secure media uploads', 'wpshadow' );
		}

		// Check 5: Verify media library scanning
		$media_scan_enabled = get_option( 'wpshadow_media_exif_scan', false );
		if ( ! $media_scan_enabled ) {
			$issues[] = __( 'Media library EXIF scanning not configured', 'wpshadow' );
		}

		// Check 6: Check automatic processing on upload
		$auto_process = get_option( 'wpshadow_auto_strip_exif_on_upload', false );
		if ( ! $auto_process ) {
			$issues[] = __( 'Automatic EXIF stripping on upload not enabled', 'wpshadow' );
				break;
			}
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 70 + ( count( $issues ) * 5 ) );
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Image EXIF data privacy issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'      => 'high',
				'threat_level'  => $threat_level,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/image-exif-data-not-stripped',
			);
		}

		return null;
	}
}
