<?php
/**
 * Media Backup Status Treatment
 *
 * Checks whether media files are included in backup routines
 * and validates backup plugin coverage.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Backup_Status Class
 *
 * Detects whether a backup plugin is active and configured
 * to include the uploads directory.
 *
 * @since 1.6093.1200
 */
class Treatment_Media_Backup_Status extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-backup-status';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Backup Status';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if media files are included in backup routines';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Backup_Status' );
	}
}
