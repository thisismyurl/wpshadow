<?php
/**
 * Import Log File Issues Treatment
 *
 * Tests whether import logs are created and accessible.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import Log File Issues Treatment Class
 *
 * Tests whether import logs are created and accessible for troubleshooting.
 *
 * @since 1.6093.1200
 */
class Treatment_Import_Log_File_Issues extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'import-log-file-issues';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Import Log File Issues';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether import logs are created and accessible';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Import_Log_File_Issues' );
	}
}
