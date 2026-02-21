<?php
/**
 * Media Direct File Access Security Treatment
 *
 * Tests if direct access to PHP files in uploads is blocked.
 * Validates .htaccess rules prevent direct PHP execution.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.2100
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Direct_File_Access_Security Class
 *
 * Checks if direct PHP file execution is blocked in the uploads directory.
 * This is a critical security control that prevents attackers from executing
 * malicious PHP scripts if they manage to upload them.
 *
 * @since 1.6033.2100
 */
class Treatment_Media_Direct_File_Access_Security extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-direct-file-access-security';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Direct File Access Security';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if direct PHP file execution is blocked in uploads directory';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media-security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2100
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Direct_File_Access_Security' );
	}
}
