<?php
/**
 * Upload Progress Screen Reader Announcements Treatment
 *
 * Tests ARIA live regions for upload progress.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Upload Progress Screen Reader Announcements Treatment Class
 *
 * Verifies that upload progress is announced to screen readers
 * using ARIA live regions and status updates.
 *
 * @since 1.6033.0000
 */
class Treatment_Upload_Progress_Screen_Reader_Announcements extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'upload-progress-screen-reader-announcements';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Upload Progress Screen Reader Announcements';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests ARIA live regions for upload progress';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Upload_Progress_Screen_Reader_Announcements' );
	}
}
