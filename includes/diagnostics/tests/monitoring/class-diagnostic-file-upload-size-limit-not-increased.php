<?php
/**
 * File Upload Size Limit Not Increased Diagnostic
 *
 * Checks if file upload size limit is optimized.
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
 * File Upload Size Limit Not Increased Diagnostic Class
 *
 * Detects low file upload limit.
 *
 * @since 0.6093.1200
 */
class Diagnostic_File_Upload_Size_Limit_Not_Increased extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'file-upload-size-limit-not-increased';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'File Upload Size Limit Not Increased';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if file upload size limit is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check maximum upload size
		$max_upload = wp_max_upload_size();
		$max_mb = $max_upload / 1048576; // Convert to MB

		if ( $max_mb < 128 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: current max upload size in MB */
					__( 'Maximum upload size is only %dMB. Increase it to 128MB or higher in wp-config.php to handle larger media files.', 'wpshadow' ),
					(int) $max_mb
				),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/file-upload-size-limit-not-increased?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
