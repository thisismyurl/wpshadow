<?php
/**
 * Video Metadata Extraction Treatment
 *
 * Tests if video metadata (duration, dimensions) is extracted and stored correctly.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video Metadata Extraction Treatment Class
 *
 * Validates that WordPress can extract and store video metadata such as
 * duration, width, height, and other important video attributes.
 *
 * @since 0.6093.1200
 */
class Treatment_Video_Metadata_Extraction extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-metadata-extraction';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Video Metadata Extraction';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates video metadata (duration, dimensions) extraction and storage';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Tests if WordPress can extract video metadata using available
	 * libraries (getID3, FFmpeg, WordPress metadata parser).
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Video_Metadata_Extraction' );
	}
}
