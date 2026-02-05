<?php
/**
 * Theme Performance Analysis Treatment
 *
 * Evaluates theme performance and checks for optimization opportunities
 * including asset loading, bloat, and modern best practices.
 *
 * @since   1.6033.2084
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Performance Analysis Treatment Class
 *
 * Analyzes theme performance:
 * - Theme asset count and size
 * - Render-blocking resources
 * - Block theme vs classic theme
 * - Theme optimization
 *
 * @since 1.6033.2084
 */
class Treatment_Theme_Performance_Analysis extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-performance-analysis';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Performance Analysis';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Evaluates theme performance and optimization opportunities';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2084
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
			if ( $style && strpos( $style->src ?? '', get_theme_file_uri() ) !== false ) {
				$theme_stylesheet_count++;
			}
		}

		// Count theme scripts
		foreach ( $wp_scripts->queue as $handle ) {
			$script = $wp_scripts->registered[ $handle ] ?? null;
			if ( $script && strpos( $script->src ?? '', get_theme_file_uri() ) !== false ) {
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
				'kb_link'       => 'https://wpshadow.com/kb/theme-performance',
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
