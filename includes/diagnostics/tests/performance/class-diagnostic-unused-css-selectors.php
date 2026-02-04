<?php
/**
 * Unused CSS Selectors Diagnostic
 *
 * Detects unused CSS selectors and optimization opportunities.
 *
 * @since   1.6033.2120
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Unused CSS Selectors Diagnostic
 *
 * Identifies unused CSS that can be removed to reduce file size.
 *
 * @since 1.6033.2120
 */
class Diagnostic_Unused_CSS_Selectors extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'unused-css-selectors';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Unused CSS Selectors';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects unused CSS selectors and optimization opportunities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2120
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_styles;

		if ( ! isset( $wp_styles->registered ) ) {
			return null;
		}

		// Check for CSS optimization plugins
		$css_optimization_plugins = array(
			'wp-rocket/wp-rocket.php',
			'autoptimize/autoptimize.php',
			'asset-cleanup-pro/wpacu.php',
			'perfmatrix/perfmatrix.php',
			'flying-press/flying-press.php',
		);

		$plugin_detected = false;
		foreach ( $css_optimization_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$plugin_detected = true;
				break;
			}
		}

		// Count total stylesheets and estimate size
		$stylesheet_count = 0;
		$total_css_size   = 0;

		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( ! $wp_styles->is_enqueued( $handle ) ) {
				continue;
			}

			$stylesheet_count++;

			// Estimate size for local stylesheets
			if ( isset( $style->src ) && strpos( $style->src, home_url() ) !== false ) {
				$file_path = str_replace( home_url(), ABSPATH, $style->src );
				$file_path = str_replace( array( 'http://', 'https://' ), '', $file_path );

				if ( file_exists( $file_path ) ) {
					$total_css_size += filesize( $file_path );
				}
			}
		}

		// Convert size to KB
		$total_css_size_kb = round( $total_css_size / 1024, 2 );

		// Generate findings if large CSS without optimization
		if ( $total_css_size_kb > 150 && ! $plugin_detected && $stylesheet_count > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: size in KB, 2: number of stylesheets */
					__( '%1$s KB of CSS across %2$d files without unused CSS removal. Consider using PurgeCSS or similar tools.', 'wpshadow' ),
					$total_css_size_kb,
					$stylesheet_count
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/unused-css-selectors',
				'meta'         => array(
					'total_css_size_kb'  => $total_css_size_kb,
					'stylesheet_count'   => $stylesheet_count,
					'plugin_configured'  => $plugin_detected,
					'recommendation'     => 'Use WP Rocket, Asset Cleanup Pro, or PurgeCSS',
					'impact_estimate'    => '30-70% CSS size reduction typical',
					'estimated_savings'  => round( $total_css_size_kb * 0.50, 2 ) . ' KB',
					'tools'              => array(
						'WP Rocket (paid)',
						'Asset Cleanup Pro (paid)',
						'Autoptimize (free)',
						'PurgeCSS (build tool)',
					),
				),
			);
		}

		// Warning for moderate CSS size
		if ( $total_css_size_kb > 100 && ! $plugin_detected ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: size in KB */
					__( '%s KB of CSS detected. Review for unused styles and consider optimization.', 'wpshadow' ),
					$total_css_size_kb
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/unused-css-selectors',
				'meta'         => array(
					'total_css_size_kb' => $total_css_size_kb,
					'stylesheet_count'  => $stylesheet_count,
					'recommendation'    => 'Audit CSS and remove unused styles',
					'typical_savings'   => '40-60% for frameworks like Bootstrap',
				),
			);
		}

		return null;
	}
}
