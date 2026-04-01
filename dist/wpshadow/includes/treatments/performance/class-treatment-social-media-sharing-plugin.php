<?php
/**
 * Social Media Sharing Plugin Implementation
 *
 * Validates that social media sharing buttons and plugins are properly configured.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Social_Media_Sharing_Plugin Class
 *
 * Checks for proper social media sharing implementation and button visibility.
 *
 * @since 0.6093.1200
 */
class Treatment_Social_Media_Sharing_Plugin extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-media-sharing-plugin';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Social Media Sharing Plugin Implementation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates social media sharing buttons and plugin setup';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'social-media';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Social_Media_Sharing_Plugin' );
	}
}
