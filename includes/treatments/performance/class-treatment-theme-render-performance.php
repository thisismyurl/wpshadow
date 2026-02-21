<?php
/**
 * Theme Render Performance Treatment
 *
 * Checks active theme for indicators of heavy render complexity.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Render Performance Treatment
 *
 * Flags themes with unusually large template counts.
 *
 * @since 1.6030.2240
 */
class Treatment_Theme_Render_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-render-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Render Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks active theme for indicators of heavy render complexity';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Render_Performance' );
	}
}
