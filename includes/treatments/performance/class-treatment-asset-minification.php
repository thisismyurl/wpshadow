<?php
/**
 * Asset Minification Treatment
 *
 * Issue #4896: CSS/JS Not Minified or Combined
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if CSS and JavaScript are minified.
 * Minification reduces file sizes by 30-50%.
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
 * Treatment_Asset_Minification Class
 *
 * @since 0.6093.1200
 */
class Treatment_Asset_Minification extends Treatment_Base {

	protected static $slug = 'asset-minification';
	protected static $title = 'CSS/JS Not Minified or Combined';
	protected static $description = 'Checks if assets are minified to reduce file sizes';
	protected static $family = 'performance';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Asset_Minification' );
	}
}
