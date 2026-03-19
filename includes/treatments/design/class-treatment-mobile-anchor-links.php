<?php
/**
 * Mobile Anchor Link Performance
 *
 * Validates smooth scrolling and anchor link behavior on mobile.
 *
 * @package    WPShadow
 * @subpackage Treatments\Navigation
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Anchor Link Performance Treatment.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Anchor_Links extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-anchor-link-performance';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Anchor Link Performance';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates anchor links for mobile';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'navigation';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Anchor_Links' );
	}
}
