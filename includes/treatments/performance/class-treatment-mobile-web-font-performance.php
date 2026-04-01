<?php
/**
 * Mobile Web Font Performance
 *
 * Optimizes web font loading strategy for mobile.
 *
 * @package    WPShadow
 * @subpackage Treatments\Performance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Web Font Performance
 *
 * Validates font-display strategy and preload hints for optimal
 * web font loading on mobile.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Web_Font_Performance extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-web-font-performance';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Web Font Performance';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Optimizes web font loading strategy for mobile';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Web_Font_Performance' );
	}
}
