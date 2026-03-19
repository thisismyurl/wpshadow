<?php
/**
 * Mobile Image Alt Text Quality Treatment
 *
 * Ensures images have descriptive alt text.
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
 * Mobile Image Alt Text Quality Treatment Class
 *
 * Ensures all images have descriptive alt text for screen reader users,
 * following WCAG1.0 requirements.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Image_Alt_Text_Quality extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-image-alt-text-quality';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Image Alt Text Quality';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure all images have descriptive alt text (WCAG1.0)';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Image_Alt_Text_Quality' );
	}
}
