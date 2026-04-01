<?php
/**
 * Media Year/Month Folder Organization Treatment
 *
 * Verifies uploads are organized into year/month folders
 * and checks folder structure integrity.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Year_Month_Folder_Organization Class
 *
 * Validates year/month folder organization for uploads.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_Year_Month_Folder_Organization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-year-month-folder-organization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Year/Month Folder Organization';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies uploads use year/month folder structure';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Year_Month_Folder_Organization' );
	}
}
