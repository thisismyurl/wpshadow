<?php
/**
 * Mobile Upload Progress Indicators Treatment
 *
 * Tests upload progress display on mobile devices.
 * Validates touch-friendly UI elements and feedback.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7029.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Upload Progress Indicators Treatment Class
 *
 * Checks if upload progress is properly displayed for mobile users
 * with touch-friendly UI elements.
 *
 * @since 1.7029.1200
 */
class Treatment_Mobile_Upload_Progress_Indicators extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-upload-progress-indicators';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Upload Progress Indicators';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests upload progress display on mobile devices';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Tests if WordPress provides adequate upload progress feedback
	 * for mobile users.
	 *
	 * @since  1.7029.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Upload_Progress_Indicators' );
	}
}
