<?php
/**
 * Audio File Upload Limits Treatment
 *
 * Validates audio file upload configuration and PHP limits for audio files.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.0940
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Audio File Upload Limits Treatment Class
 *
 * Validates that WordPress has appropriate upload limits configured
 * for audio files including MP3, WAV, OGG, and other audio formats.
 *
 * @since 1.7034.0940
 */
class Treatment_Audio_File_Upload_Limits extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'audio-file-upload-limits';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Audio File Upload Limits';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates audio file upload configuration and PHP limits';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Tests if WordPress allows audio uploads and if PHP limits
	 * are sufficient for typical audio file sizes.
	 *
	 * @since  1.7034.0940
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Audio_File_Upload_Limits' );
	}
}
