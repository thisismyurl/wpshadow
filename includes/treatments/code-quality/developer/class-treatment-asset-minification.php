<?php
/**
 * Asset Minification Treatment
 *
 * Checks if CSS/JS assets are minified and properly cached.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1300
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asset Minification Treatment Class
 *
 * Verifies that CSS and JavaScript assets are minified and properly
 * cached for optimal performance.
 *
 * @since 1.6035.1300
 */
class Treatment_Asset_Minification extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'asset-minification';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Asset Minification';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CSS/JS assets are minified and properly cached';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the asset minification treatment check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if minification issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Asset_Minification' );
	}
}
