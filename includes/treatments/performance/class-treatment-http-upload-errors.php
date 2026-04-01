<?php
/**
 * HTTP Upload Errors Treatment
 *
 * Detects HTTP errors during upload process by monitoring for
 * 413 (too large), 502 (bad gateway), 504 (timeout) errors.
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
 * HTTP Upload Errors Class
 *
 * Monitors for HTTP errors during file uploads that indicate server
 * configuration issues requiring adjustment.
 *
 * @since 0.6093.1200
 */
class Treatment_HTTP_Upload_Errors extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'http-upload-errors';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP Upload Errors';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects HTTP errors during upload process';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Checks for common HTTP errors during uploads and validates
	 * server configuration to prevent upload failures.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if upload errors detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_HTTP_Upload_Errors' );
	}
}
