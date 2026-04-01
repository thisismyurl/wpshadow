<?php
/**
 * Social Media Sharing Buttons Not Configured Treatment
 *
 * Checks if social sharing is configured.
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
 * Social Media Sharing Buttons Not Configured Treatment Class
 *
 * Detects unconfigured social sharing.
 *
 * @since 0.6093.1200
 */
class Treatment_Social_Media_Sharing_Buttons_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-media-sharing-buttons-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Social Media Sharing Buttons Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if social sharing is configured';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Social_Media_Sharing_Buttons_Not_Configured' );
	}
}
