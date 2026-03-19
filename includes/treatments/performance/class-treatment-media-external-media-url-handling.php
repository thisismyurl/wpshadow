<?php
/**
 * Media External Media URL Handling Treatment
 *
 * Tests handling of external/remote media URLs and
 * validates that remote files are reachable.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_Request_Helper;
use WPShadow\Treatments\Helpers\Treatment_URL_And_Pattern_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_External_Media_URL_Handling Class
 *
 * Checks for external media URLs and validates access.
 *
 * @since 1.6093.1200
 */
class Treatment_Media_External_Media_URL_Handling extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-external-media-url-handling';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'External Media URL Handling';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests handling of external or remote media URLs';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_External_Media_URL_Handling' );
	}
}
