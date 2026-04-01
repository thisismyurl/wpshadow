<?php
/**
 * Theme Responsive Design Treatment
 *
 * Checks if theme is responsive and mobile-friendly.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Responsive Design Treatment Class
 *
 * Analyzes theme's responsive design implementation.
 *
 * @since 0.6093.1200
 */
class Treatment_Theme_Responsive_Design_Check extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-responsive-design-check';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Responsive Design Check';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks theme responsive design';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Responsive_Design_Check' );
	}
}
