<?php
/**
 * No Minification of Assets Diagnostic
 *
 * Detects when CSS/JS files are not minified,
 * causing unnecessarily large file sizes.
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
 * Diagnostic: No Minification of Assets
 *
 * Checks whether CSS and JavaScript files
 * are being minified for faster loading.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Minification_Of_Assets extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-minification-assets';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Asset Minification (CSS/JS)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether CSS/JS files are minified';

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
			is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ||
			is_plugin_active( 'wp-fastest-cache/wpfc.php' );

		// Check homepage for unminified files
		$homepage = wp_remote_get( home_url() );
		if ( ! is_wp_error( $homepage ) ) {
			$body = wp_remote_retrieve_body( $homepage );
			// Look for .css and .js files (not .min.css or .min.js)
			preg_match_all( '/href=["\']([^"\']*\.css)["\']|src=["\']([^"\']*\.js)["\']/', $body, $matches );
			
			$has_unminified = false;
			foreach ( $matches[0] as $match ) {
				if ( strpos( $match, '.min.' ) === false ) {
					$has_unminified = true;
					break;
				}
			}
		} else {
			$has_unminified = false;
		}

		if ( ! $has_minification && $has_unminified ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Your CSS and JavaScript files aren\'t minified, which means they\'re 30-50% larger than necessary. Minification removes: whitespace, comments, line breaks, shortens variable names. A 100KB CSS file becomes 60KB when minified. For sites with lots of CSS/JS, this saves hundreds of KB per page load. Minification is automatic with plugins—enable once, forget about it.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'File Size Reduction',
					'potential_gain' => '30-50% smaller CSS/JS files',
					'roi_explanation' => 'Minification removes unnecessary characters from CSS/JS, reducing file size by 30-50% with zero effort.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/asset-minification',
			);
		}

		return null;
	}
}
