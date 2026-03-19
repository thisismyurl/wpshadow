<?php
/**
 * External Media URL Handling Treatment
 *
 * Tests handling of external/remote media URLs.
 *
 * @package    WPShadow
 * @subpackage Treatments\Media
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_Request_Helper;
use WPShadow\Core\Treatment_Base;
use WPShadow\Treatments\Helpers\Treatment_URL_And_Pattern_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_External_Media_URL_Handling Class
 *
 * Detects attachments pointing to external URLs and tests accessibility.
 * External URLs can break if hotlinking is blocked or remote hosts go down.
 *
 * @since 1.6093.1200
 */
class Treatment_External_Media_URL_Handling extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'external-media-url-handling';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'External Media URL Handling';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests handling of external/remote media URLs';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Validates:
	 * - External media URLs
	 * - Hotlink accessibility
	 * - Mixed protocol issues
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_External_Media_URL_Handling' );
	}
}
