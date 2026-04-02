<?php
/**
 * Duplicate Filename Handling Treatment
 *
 * Tests how WordPress handles duplicate filenames during upload and
 * verifies filename sanitization works correctly.
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
 * Duplicate Filename Handling Class
 *
 * Ensures WordPress properly handles duplicate filenames by adding
 * numeric suffixes and sanitizes filenames to prevent security issues.
 *
 * @since 1.6093.1200
 */
class Treatment_Duplicate_Filename_Handling extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'duplicate-filename-handling';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate Filename Handling';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests WordPress duplicate filename handling';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Validates filename sanitization and duplicate handling by checking
	 * for common issues with uploaded files.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if filename issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Duplicate_Filename_Handling' );
	}
}
