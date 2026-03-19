<?php
/**
 * PHP Upload Limits Diagnostic
 *
 * Checks if PHP upload limits are sufficient for media and plugin uploads.
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
 * PHP Upload Limits Diagnostic Class
 *
 * Verifies PHP upload limits allow for media, themes, and plugins.
 * Like the size limit on email attachments—too small blocks uploads.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Php_Upload_Limits extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'php-upload-limits';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP Upload Limits';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP upload limits are sufficient for media and plugin uploads';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'hosting';

	/**
	 * Run the PHP upload limits diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if upload limit issues detected, null otherwise.
	 */
	public static function check() {
		$upload_max_filesize = ini_get( 'upload_max_filesize' );
		$post_max_size       = ini_get( 'post_max_size' );
		
		$upload_bytes = wp_convert_hr_to_bytes( $upload_max_filesize );
		$post_bytes   = wp_convert_hr_to_bytes( $post_max_size );
		
		$upload_mb = $upload_bytes / 1024 / 1024;
		$post_mb   = $post_bytes / 1024 / 1024;

		$min_recommended = 64;
		$preferred       = 128;

		$issues = array();

		// Check upload_max_filesize.
		if ( $upload_mb < $min_recommended ) {
			$issues[] = sprintf(
				/* translators: %s: current upload limit in MB */
				__( 'File upload limit is %s MB (like a mailbox slot that\'s too small for large packages)', 'wpshadow' ),
				number_format_i18n( $upload_mb, 0 )
			);
		}

		// Check post_max_size.
		if ( $post_mb < $min_recommended ) {
			$issues[] = sprintf(
				/* translators: %s: current post size limit in MB */
				__( 'Form submission limit is %s MB', 'wpshadow' ),
				number_format_i18n( $post_mb, 0 )
			);
		}

		// Check if post_max_size < upload_max_filesize (configuration error).
		if ( $post_mb < $upload_mb ) {
			$issues[] = __( 'Form submission limit is smaller than file upload limit (misconfiguration)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$severity = $upload_mb < 8 ? 'high' : 'medium';
			$threat_level = $upload_mb < 8 ? 70 : 50;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: minimum recommended upload size */
					__( 'Your file upload limits could be higher to handle larger media files, themes, and plugins. Current limit: %1$s MB. We recommend at least %2$s MB (like increasing the size limit on email attachments). Without this, you won\'t be able to upload high-resolution images or video files. Contact your hosting provider to increase these limits.', 'wpshadow' ),
					number_format_i18n( $upload_mb, 0 ),
					$min_recommended
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/php-upload-limits',
				'context'      => array(
					'upload_max_mb'   => $upload_mb,
					'post_max_mb'     => $post_mb,
					'recommended'     => $min_recommended,
					'issues'          => $issues,
				),
			);
		}

		return null; // Upload limits are adequate.
	}
}
