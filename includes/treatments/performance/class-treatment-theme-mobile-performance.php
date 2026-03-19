<?php
/**
 * Theme Mobile Performance Treatment
 *
 * Checks if the active theme is optimized for mobile devices including
 * viewport meta tags, mobile-responsive stylesheets, and touch-friendly
 * navigation elements.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Mobile Performance Treatment Class
 *
 * Validates mobile optimization features in the active theme.
 *
 * @since 1.6093.1200
 */
class Treatment_Theme_Mobile_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-mobile-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Mobile Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates mobile optimization in active theme';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Mobile_Performance' );
	}
}
