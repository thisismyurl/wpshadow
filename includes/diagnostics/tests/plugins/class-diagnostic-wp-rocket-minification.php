<?php
/**
 * WP Rocket Minification Diagnostic
 *
 * WP Rocket minification breaking scripts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.439.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Rocket Minification Diagnostic Class
 *
 * @since 1.439.0000
 */
class Diagnostic_WpRocketMinification extends Diagnostic_Base {

	protected static $slug = 'wp-rocket-minification';
	protected static $title = 'WP Rocket Minification';
	protected static $description = 'WP Rocket minification breaking scripts';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WP_ROCKET_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Get WP Rocket options
		$options = get_option( 'wp_rocket_settings', array() );
		if ( empty( $options ) ) {
			$issues[] = 'settings_unavailable';
			$threat_level += 15;
			return $this->build_finding( $issues, $threat_level );
		}

		// Check CSS minification
		$minify_css = isset( $options['minify_css'] ) ? $options['minify_css'] : 0;
		$minify_concatenate_css = isset( $options['minify_concatenate_css'] ) ? $options['minify_concatenate_css'] : 0;
		if ( ! $minify_css ) {
			$issues[] = 'css_minification_disabled';
			$threat_level += 15;
		}

		// Check JS minification
		$minify_js = isset( $options['minify_js'] ) ? $options['minify_js'] : 0;
		$minify_concatenate_js = isset( $options['minify_concatenate_js'] ) ? $options['minify_concatenate_js'] : 0;
		if ( ! $minify_js ) {
			$issues[] = 'js_minification_disabled';
			$threat_level += 15;
		}

		// Check defer JS loading
		$defer_all_js = isset( $options['defer_all_js'] ) ? $options['defer_all_js'] : 0;
		if ( ! $defer_all_js ) {
			$issues[] = 'js_not_deferred';
			$threat_level += 10;
		}

		// Check excluded files (too many exclusions reduces benefit)
		$excluded_css = isset( $options['exclude_css'] ) ? $options['exclude_css'] : array();
		$excluded_js = isset( $options['exclude_js'] ) ? $options['exclude_js'] : array();
		if ( count( $excluded_css ) > 5 || count( $excluded_js ) > 5 ) {
			$issues[] = 'excessive_exclusions';
			$threat_level += 10;
		}

		// Check minified file directory
		$upload_dir = wp_upload_dir();
		$cache_dir = $upload_dir['basedir'] . '/wp-rocket-cache';
		if ( ! is_dir( $cache_dir ) || ! is_writable( $cache_dir ) ) {
			$issues[] = 'cache_directory_not_writable';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of minification issues */
				__( 'WP Rocket minification has issues: %s. This can cause slower page loads, render-blocking resources, and missed optimization opportunities.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-rocket-minification',
			);
		}
		
		return null;
	}
}
