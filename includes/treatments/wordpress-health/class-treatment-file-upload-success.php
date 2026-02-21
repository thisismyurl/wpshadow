<?php
/**
 * File Upload Success Treatment
 *
 * Verifies that the uploads directory is writable and correctly configured.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1345
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_File_Upload_Success Class
 *
 * Ensures WordPress can write to the uploads directory.
 *
 * @since 1.6035.1345
 */
class Treatment_File_Upload_Success extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'file-upload-success';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'File Upload Success';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether uploads directory is writable';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'wordpress-health';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1345
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_File_Upload_Success' );
	}
}