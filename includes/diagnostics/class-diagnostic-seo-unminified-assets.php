<?php declare(strict_types=1);
/**
 * Unminified CSS/JS Diagnostic
 *
 * Philosophy: SEO performance - minification improves page speed
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if CSS/JS is minified.
 */
class Diagnostic_SEO_Unminified_Assets {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wp_scripts, $wp_styles;
		
		$unminified = 0;
		
		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( strpos( $script->src, '.min.js' ) === false && strpos( $script->src, '.js' ) !== false ) {
					$unminified++;
				}
			}
		}
		
		if ( $unminified > 5 ) {
			return array(
				'id'          => 'seo-unminified-assets',
				'title'       => 'Unminified CSS/JS Files',
				'description' => sprintf( '%d unminified assets detected. Minification reduces file size by 20-40%%. Use plugin like Autoptimize or WP Rocket to minify CSS/JS.', $unminified ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/minify-assets/',
				'training_link' => 'https://wpshadow.com/training/asset-optimization/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
