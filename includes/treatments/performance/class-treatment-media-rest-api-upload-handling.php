<?php
/**
 * Media REST API Upload Handling Treatment
 *
 * Checks if REST API media uploads are properly validated and sanitized.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media REST API Upload Handling Treatment Class
 *
 * Verifies that file uploads via REST API are properly validated,
 * sanitized, and stored securely with appropriate metadata.
 *
 * @since 1.6033.0000
 */
class Treatment_Media_Rest_Api_Upload_Handling extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-rest-api-upload-handling';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media REST API Upload Handling';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if REST API media uploads are properly validated and sanitized';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Rest_Api_Upload_Handling' );
	}
}
