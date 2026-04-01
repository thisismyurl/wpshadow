<?php
/**
 * Media Headless CMS Serving Treatment
 *
 * Checks if media is properly exposed for headless/decoupled WordPress usage.
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
 * Media Headless CMS Serving Treatment Class
 *
 * Verifies that media files and metadata are properly exposed via REST API
 * for headless WordPress implementations with proper CORS and authentication.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_Headless_Cms_Serving extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-headless-cms-serving';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Headless CMS Serving';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if media is properly exposed for headless/decoupled WordPress usage';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Headless_Cms_Serving' );
	}
}
