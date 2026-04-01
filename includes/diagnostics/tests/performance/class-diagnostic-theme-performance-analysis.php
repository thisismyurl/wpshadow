<?php
/**
 * Theme Performance Analysis Diagnostic
 *
 * Evaluates theme performance and checks for optimization opportunities
 * including asset loading, bloat, and modern best practices.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Performance Analysis Diagnostic Class
 *
 * Analyzes theme performance:
 * - Theme asset count and size
 * - Render-blocking resources
 * - Block theme vs classic theme
 * - Theme optimization
 *
 * @since 0.6093.1200
 */
class Diagnostic_Theme_Performance_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-performance-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Performance Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Evaluates theme performance and optimization opportunities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		global $wp_styles, $wp_scripts;

		$current_theme = wp_get_theme();
		$is_block_theme = $current_theme->is_block_theme();
		$theme_stylesheet_count = 0;
		$theme_script_count = 0;

		// Count theme stylesheets
		foreach ( $wp_styles->queue as $handle ) {
			$style = $wp_styles->registered[ $handle ] ?? null;
			if ( $style && isset( $style->src ) && is_string( $style->src ) && strpos( $style->src, get_theme_file_uri() ) !== false ) {
				$theme_stylesheet_count++;
			}
		}

		// Count theme scripts
		foreach ( $wp_scripts->queue as $handle ) {
			$script = $wp_scripts->registered[ $handle ] ?? null;
			if ( $script && isset( $script->src ) && is_string( $script->src ) && strpos( $script->src, get_theme_file_uri() ) !== false ) {
				$theme_script_count++;
			}
		}

		// Flag if theme is loading too many assets
		if ( ( $theme_stylesheet_count > 5 || $theme_script_count > 8 ) && ! $is_block_theme ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: stylesheets, %d: scripts */
					__( 'Theme is loading %d stylesheets and %d scripts. This may indicate bloat. Consider a performance-optimized theme.', 'wpshadow' ),
					$theme_stylesheet_count,
					$theme_script_count
				),
				'severity'      => 'low',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/theme-performance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'          => array(
					'current_theme'        => $current_theme->get( 'Name' ),
					'is_block_theme'       => $is_block_theme,
					'stylesheets'          => $theme_stylesheet_count,
					'scripts'              => $theme_script_count,
					'recommendation'       => 'Consider lightweight themes like: GeneratePress, Astra, Neve, or block-based themes',
					'impact'               => 'Switching to lean theme can improve TTI by 500ms-1s',
					'optimization'         => array(
						'Combine theme CSS files',
						'Minify and defer theme JS',
						'Remove unused theme fonts',
						'Use block theme for modern sites',
					),
				),
			);
		}

		return null;
	}
}
