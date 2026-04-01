<?php
/**
 * Media Version Tracking Treatment
 *
 * Tests media file versioning and revision history.
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
 * Media Version Tracking Treatment Class
 *
 * Verifies media file versioning and revision history,
 * including replacement tracking and rollback functionality.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_Version_Tracking extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-version-tracking';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Version Tracking';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media file versioning and revision history';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Version_Tracking' );
	}
}
