<?php
/**
 * REST API Media Upload Treatment
 *
 * Detects if REST API media upload endpoints are properly configured and secured.
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
 * Treatment_REST_API_Media_Upload Class
 *
 * Tests if REST API media upload endpoints enforce file type restrictions,
 * size limits, and proper security checks before accepting uploads.
 *
 * @since 0.6093.1200
 */
class Treatment_REST_API_Media_Upload extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-media-upload';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Media Upload';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies REST API media uploads are properly secured';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_REST_API_Media_Upload' );
	}
}
