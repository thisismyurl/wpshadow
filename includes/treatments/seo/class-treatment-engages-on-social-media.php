<?php
/**
 * Treatment: Social Media Engagement
 *
 * Tests if site actively engages on social media platforms.
 * Social media extends reach and builds brand awareness.
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
 * Social Media Engagement Treatment Class
 *
 * Checks if site has social media integration and encourages
 * social sharing and engagement.
 *
 * Detection methods:
 * - Social sharing buttons
 * - Social media links
 * - Social auto-posting
 * - Social feed integration
 *
 * @since 0.6093.1200
 */
class Treatment_Engages_On_Social_Media extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'engages-on-social-media';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Social Media Engagement';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site actively engages on social media platforms';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-engagement';

	/**
	 * Run the treatment check.
	 *
	 * Scoring system (5 points):
	 * - 1 point: Social sharing buttons present
	 * - 1 point: Social media auto-posting enabled
	 * - 1 point: Social media feeds embedded
	 * - 1 point: Click to Tweet or similar features
	 * - 1 point: Open Graph tags configured
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Engages_On_Social_Media' );
	}
}
