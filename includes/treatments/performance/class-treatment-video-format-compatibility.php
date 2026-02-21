<?php
/**
 * Video Format Compatibility Treatment
 *
 * Tests supported video formats (MP4, WebM, OGG) and browser compatibility.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.0910
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video Format Compatibility Treatment Class
 *
 * Validates that WordPress is configured to support multiple video formats
 * (MP4, WebM, OGG) for maximum browser compatibility.
 *
 * @since 1.7034.0910
 */
class Treatment_Video_Format_Compatibility extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-format-compatibility';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Video Format Compatibility';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates supported video formats (MP4, WebM, OGG) and browser playback';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Tests if WordPress allows commonly supported video formats and
	 * checks for FFmpeg/codec availability for transcoding.
	 *
	 * @since  1.7034.0910
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Video_Format_Compatibility' );
	}
}
