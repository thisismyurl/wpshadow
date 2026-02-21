<?php
/**
 * Media CDN Not Configured Treatment
 *
 * Checks if media files are served via CDN.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.2033.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_CDN_Not_Configured Class
 *
 * Detects when media files lack CDN delivery.
 * CDN improves page load speed and reduces server bandwidth costs.
 *
 * @since 1.2033.0000
 */
class Treatment_Media_CDN_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-cdn-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media CDN Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if media is served via CDN';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Checks for:
	 * - Jetpack Photon/Image CDN
	 * - WP Rocket CDN integration
	 * - Cloudflare configuration
	 * - Custom CDN URL settings
	 *
	 * @since  1.2033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_CDN_Not_Configured' );
	}
}
