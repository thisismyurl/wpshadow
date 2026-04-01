<?php
/**
 * Upload Progress Tracking Treatment
 *
 * Verifies upload progress bar works correctly. Tests JavaScript upload handlers.
 *
 * @package    WPShadow
 * @subpackage Treatments\Media
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Upload_Progress_Tracking Class
 *
 * Validates upload progress tracking functionality. WordPress uses Plupload
 * for asynchronous uploads with progress indicators. Issues with JavaScript
 * handlers or session support can break progress tracking.
 *
 * @since 0.6093.1200
 */
class Treatment_Upload_Progress_Tracking extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'upload-progress-tracking';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Upload Progress Tracking';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies upload progress bar works correctly';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Validates:
	 * - PHP session support for progress tracking
	 * - Plupload script enqueued
	 * - wp-ajax endpoint availability
	 * - JavaScript error logs
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Upload_Progress_Tracking' );
	}
}
