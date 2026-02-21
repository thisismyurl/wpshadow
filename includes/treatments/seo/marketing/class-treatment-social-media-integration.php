<?php
/**
 * Social Media Integration Treatment
 *
 * Detects when social media profiles aren't integrated on the website.
 *
 * @package    WPShadow
 * @subpackage Treatments\Marketing
 * @since      1.6035.2308
 */

declare(strict_types=1);

namespace WPShadow\Treatments\Marketing;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social Media Integration Treatment Class
 *
 * Checks if social media profiles are linked and integrated on the site.
 *
 * @since 1.6035.2308
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
	protected static $title = 'No Social Media Integration on Website';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when social media profiles aren\'t linked or integrated';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6035.2308
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Marketing\Diagnostic_Social_Media_Integration' );
	}
}
