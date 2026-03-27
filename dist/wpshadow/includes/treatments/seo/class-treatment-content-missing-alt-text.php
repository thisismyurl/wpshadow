<?php
/**
 * Content Missing Alt Text Treatment
 *
 * Detects images without accessibility-required alt text.
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
 * Content Missing Alt Text Treatment Class
 *
 * Images without alt text fail accessibility (WCAG) and SEO.
 * Screen readers can't describe images. 15% of users affected.
 *
 * @since 1.6093.1200
 */
class Treatment_Content_Missing_Alt_Text extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-missing-alt-text';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Alt Text on Images';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detect images without alt text (WCAG compliance & SEO impact)';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Content_Missing_Alt_Text' );
	}
}
