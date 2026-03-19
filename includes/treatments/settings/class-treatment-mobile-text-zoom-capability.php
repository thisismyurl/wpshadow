<?php
/**
 * Mobile Text Zoom Capability Treatment
 *
 * Ensures text scales to 200% when user zooms without horizontal scroll.
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
 * Mobile Text Zoom Capability Treatment Class
 *
 * Ensures text scales to 200% when user zooms without horizontal scroll,
 * a critical WCAG AA requirement for low-vision users.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Text_Zoom_Capability extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-text-zoom-capability';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Text Zoom Capability';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure text scales to 200% when user zooms without horizontal scroll (WCAG1.0)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Text_Zoom_Capability' );
	}
}
