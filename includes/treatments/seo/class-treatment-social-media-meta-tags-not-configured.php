<?php
/**
 * Social Media Meta Tags Not Configured Treatment
 *
 * Checks if social media meta tags are configured.
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
 * Social Media Meta Tags Not Configured Treatment Class
 *
 * Detects missing social media meta tags.
 *
 * @since 0.6093.1200
 */
class Treatment_Social_Media_Meta_Tags_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-media-meta-tags-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Social Media Meta Tags Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if social media meta tags are configured';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Social_Media_Meta_Tags_Not_Configured' );
	}
}
