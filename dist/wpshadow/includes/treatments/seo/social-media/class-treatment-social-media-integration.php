<?php
/**
 * Social Media Integration Treatment
 *
 * Checks if social media sharing and integration is configured.
 *
 * @package WPShadow\Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

/**
 * Treatment: Social Media Integration
 *
 * Detects whether the site has social media sharing and integration features.
 */
class Treatment_Social_Media_Integration extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-media-integration';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Social Media Integration';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for social media sharing and integration';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'social-media';

	/**
	 * Run the treatment check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Marketing\Diagnostic_Social_Media_Integration' );
	}
}
