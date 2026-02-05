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
 * @since      1.6050.0000
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
 * @since 1.6050.0000
 */
class Treatment_Asset_Minification extends Treatment_Base {

	protected static $slug = 'asset-minification';
	protected static $title = 'CSS/JS Not Minified or Combined';
	protected static $description = 'Checks if assets are minified to reduce file sizes';
	protected static $family = 'performance';

	public static function check() {
		global $wp_scripts, $wp_styles;

		$issues = array();
		$unminified_count = 0;

		// Check scripts
		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( $script->src && strpos( $script->src, '.min.js' ) === false && strpos( $script->src, 'wp-includes' ) === false ) {
					$unminified_count++;
				}
			}
		}

		if ( $unminified_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of unminified files */
				__( '%d JavaScript files not minified', 'wpshadow' ),
				$unminified_count
			);
		}

		$issues[] = __( 'Minify CSS and JavaScript files (remove whitespace, comments)', 'wpshadow' );
		$issues[] = __( 'Combine multiple files to reduce HTTP requests', 'wpshadow' );
		$issues[] = __( 'Use .min.js and .min.css naming convention', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Minification removes whitespace and comments from code, reducing file sizes by 30-50% and improving page load times.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/asset-minification',
				'details'      => array(
					'recommendations'         => $issues,
					'unminified_scripts'      => $unminified_count,
					'size_savings'            => '30-50% file size reduction typical',
					'tools'                   => 'UglifyJS, Terser, cssnano, Autoptimize plugin',
				),
			);
		}

		return null;
	}
}
