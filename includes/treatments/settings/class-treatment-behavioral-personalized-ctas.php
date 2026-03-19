<?php
/**
 * Treatment: Personalized CTAs
 *
 * Tests whether the site personalizes calls-to-action based on user context
 * to outperform generic CTAs by 202%.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4537
 *
 * @package    WPShadow
 * @subpackage Treatments\Behavioral
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Personalized CTAs Treatment
 *
 * Checks if CTAs adapt based on user behavior, location, referrer, or
 * previous visits. Personalized CTAs convert 202% better than generic ones.
 *
 * @since 1.6093.1200
 */
class Treatment_Behavioral_Personalized_CTAs extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'personalizes-call-to-action';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Personalized CTAs';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site personalizes CTAs based on user context';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for CTA personalization.
	 *
	 * Detects personalization engines, dynamic content plugins, and
	 * behavior-based customization.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if not implemented, null if present.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Behavioral_Personalized_CTAs' );
	}
}
