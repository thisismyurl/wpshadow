<?php
/**
 * No Asset Minification Diagnostic
 *
 * Detects when CSS/JS assets are not minified,
 * causing unnecessarily large file transfers.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Asset Minification
 *
 * Checks whether CSS and JavaScript assets
 * are minified for optimal delivery.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Asset_Minification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-asset-minification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Asset Minification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether assets are minified';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for minification plugins
		$has_minification = is_plugin_active( 'autoptimize/autoptimize.php' ) ||
			is_plugin_active( 'wp-rocket/wp-rocket.php' ) ||
			is_plugin_active( 'fast-velocity-minify/fvm.php' );

		if ( ! $has_minification ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'CSS/JS assets aren\'t minified, which causes unnecessarily large transfers. Minification removes: whitespace, comments, line breaks, shortens variable names. This reduces file size by 30-60%. Example: 100KB CSS becomes 40KB minified. Also enables: concatenation (combine multiple files), GZIP compression (better on minified files). Plugins: Autoptimize (free), WP Rocket, Fast Velocity Minify. This is low-hanging performance fruit.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Asset Transfer Size',
					'potential_gain' => '30-60% smaller CSS/JS files',
					'roi_explanation' => 'Minification reduces CSS/JS file sizes 30-60% by removing whitespace and optimizing code.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/asset-minification',
			);
		}

		return null;
	}
}
