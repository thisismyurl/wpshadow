<?php
/**
 * Mobile Render-Blocking CSS Detection
 *
 * Finds CSS files blocking render on mobile.
 *
 * @package    WPShadow
 * @subpackage Treatments\Performance
 * @since      1.602.1600
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Render-Blocking CSS Detection
 *
 * Identifies CSS files loaded in <head> that block render
 * and should be deferred or inlined for mobile.
 *
 * @since 1.602.1600
 */
class Treatment_Mobile_Render_Blocking_CSS extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-render-blocking-css';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Render-Blocking CSS Detection';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Finds CSS files blocking render on mobile';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1600
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Render_Blocking_CSS' );
	}
}
