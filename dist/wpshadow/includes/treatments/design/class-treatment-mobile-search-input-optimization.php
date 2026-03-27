<?php
/**
 * Mobile Search Input Optimization Treatment
 *
 * Validates that search inputs are optimized for mobile with proper keyboard,
 * autocomplete, and visual design.
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Search Input Optimization Treatment Class
 *
 * Checks search forms for mobile-specific optimizations including input type,
 * autocomplete, button size, and keyboard appearance.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Search_Input_Optimization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-search-input-optimization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Search Input Optimization';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that search inputs are optimized for mobile with proper keyboard, autocomplete, and design';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Search_Input_Optimization' );
	}
}
