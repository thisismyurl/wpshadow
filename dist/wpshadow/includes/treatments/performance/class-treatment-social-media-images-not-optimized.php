<?php
/**
 * Social Media Images Not Optimized Treatment
 *
 * Detects when posts lack properly sized social media images
 * (Open Graph, Twitter Cards), resulting in poor social sharing.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social Media Images Not Optimized Treatment Class
 *
 * Checks if posts have properly sized social media images. Social
 * platforms require specific dimensions for optimal display.
 *
 * @since 1.6093.1200
 */
class Treatment_Social_Media_Images_Not_Optimized extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-media-images-not-optimized';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Social Media Images Not Optimized';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects posts missing or improperly sized social media images';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * Checks if posts have social media images (og:image, twitter:image).
	 * Properly sized images improve CTR by up to 40%.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Social_Media_Images_Not_Optimized' );
	}
}
