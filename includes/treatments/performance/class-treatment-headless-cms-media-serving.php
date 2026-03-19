<?php
/**
 * Headless CMS Media Serving Treatment
 *
 * Tests media delivery for headless WordPress setups.
 * Validates CORS and authentication configuration.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_Request_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Headless CMS Media Serving Treatment Class
 *
 * Checks if media is properly configured for headless WordPress
 * with appropriate CORS and authentication.
 *
 * @since 1.6093.1200
 */
class Treatment_Headless_CMS_Media_Serving extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'headless-cms-media-serving';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Headless CMS Media Serving';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media delivery for headless WordPress setups';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Tests if WordPress is configured for headless CMS usage
	 * with proper CORS and media serving.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Headless_CMS_Media_Serving' );
	}
}
