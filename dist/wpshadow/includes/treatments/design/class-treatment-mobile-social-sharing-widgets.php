<?php
/**
 * Mobile Social Sharing Widgets Treatment
 *
 * Optimizes social sharing widgets for mobile devices.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Social Sharing Widgets Treatment Class
 *
 * Validates social sharing widgets are optimized for mobile with proper button
 * sizing and layout, ensuring WCAG 2.5.5 compliance.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Social_Sharing_Widgets extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-social-sharing-widgets';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Social Sharing Widgets';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Optimize social sharing widgets for mobile with proper button sizing and layout';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Social_Sharing_Widgets' );
	}
}
