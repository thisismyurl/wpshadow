<?php
/**
 * Alt Text Coverage Treatment
 *
 * Measures percentage of images with alt text.
 *
 * @package    WPShadow
 * @subpackage Treatments\Media
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Alt_Text_Coverage Class
 *
 * Checks how many images have alt text. Missing alt text reduces accessibility
 * and SEO. WCAG recommends meaningful alt text for informative images.
 *
 * @since 1.6030.2148
 */
class Treatment_Alt_Text_Coverage extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'alt-text-coverage';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Alt Text Coverage';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Measures percentage of images with alt text';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Validates:
	 * - Percentage of images with alt text
	 * - Recent uploads missing alt text
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Alt_Text_Coverage' );
	}
}
