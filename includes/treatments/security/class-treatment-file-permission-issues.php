<?php
/**
 * File Permission Issues Treatment
 *
 * Checks for risky file and directory permissions.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1485
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_File_Permission_Issues Class
 *
 * Flags world-writable permissions on key directories.
 *
 * @since 1.6035.1485
 */
class Treatment_File_Permission_Issues extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'file-permission-issues';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'File Permission Issues';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for insecure file and directory permissions';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1485
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_File_Permission_Issues' );
	}
}