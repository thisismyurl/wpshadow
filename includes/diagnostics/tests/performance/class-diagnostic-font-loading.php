<?php
/**
 * Font Loading Diagnostic
 *
 * Checks whether fonts are loaded optimally using font-display strategies
 * and whether external font dependencies are reduced via local hosting.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Font_Loading Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Font_Loading extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'font-loading';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Font Loading';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether web fonts are loaded with an optimal font-display strategy and whether external font dependencies are managed to avoid render-blocking requests.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

/**
 * Confidence level of this diagnostic.
 *
 * @var string
 */
protected static $confidence = 'standard';

	/**
	 * Font-display values that avoid invisible text (FOIT) during font loading.
	 *
	 * @var string[]
	 */
	private const GOOD_DISPLAY_VALUES = array( 'swap', 'fallback', 'optional' );

	/**
	 * Plugins that handle font optimisation or local hosting.
	 *
	 * @var array<string,string>
	 */
	private const FONT_PLUGINS = array(
		'omgf/omgf.php'                        => 'OMGF Pro',
		'omgf-pro/omgf.php'                    => 'OMGF Pro',
		'host-webfonts-local/index.php'        => 'OMGF',
		'perfmatters/perfmatters.php'          => 'Perfmatters',
		'wp-rocket/wp-rocket.php'              => 'WP Rocket',
		'flying-press/flying-press.php'        => 'FlyingPress',
		'nitropack/nitropack-plugin.php'       => 'NitroPack',
		'asset-cleanup/asset-cleanup.php'      => 'Asset CleanUp',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Passes if a font optimisation plugin is active. Falls back to scanning
	 * theme CSS for @font-face declarations that are missing font-display.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( self::FONT_PLUGINS as $plugin_file => $name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return null;
			}
		}

		// Scan theme CSS for @font-face rules missing an acceptable font-display.
		$template_dirs = array_filter(
			array_unique( array( get_stylesheet_directory(), get_template_directory() ) ),
			'is_dir'
		);

		$has_font_face          = false;
		$missing_display_files  = array();

		foreach ( $template_dirs as $dir ) {
			try {
				$iterator = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS )
				);
				foreach ( $iterator as $file ) {
					if ( 'css' !== strtolower( $file->getExtension() ) ) {
						continue;
					}
					$contents = file_get_contents( $file->getPathname() );
					if ( false === $contents || ! str_contains( $contents, '@font-face' ) ) {
						continue;
					}
					$has_font_face = true;

					// Check each @font-face block for font-display.
					preg_match_all( '/@font-face\s*\{([^}]*)\}/s', $contents, $matches );
					foreach ( $matches[1] as $block ) {
						if ( ! str_contains( $block, 'font-display' ) ) {
							$missing_display_files[] = basename( $file->getPathname() );
							break;
						}
						// Check display value is acceptable.
						$acceptable = false;
						foreach ( self::GOOD_DISPLAY_VALUES as $value ) {
							if ( str_contains( $block, $value ) ) {
								$acceptable = true;
								break;
							}
						}
						if ( ! $acceptable ) {
							$missing_display_files[] = basename( $file->getPathname() );
							break;
						}
					}
				}
			} catch ( \Exception $e ) {
				continue;
			}
		}

		// No @font-face found in theme — cannot determine usage, pass.
		if ( ! $has_font_face ) {
			return null;
		}

		if ( empty( $missing_display_files ) ) {
			return null;
		}

		$missing_display_files = array_unique( $missing_display_files );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of CSS file names */
				__( '@font-face declarations in %s are missing a font-display property. Without it, browsers default to "block" behaviour, causing invisible text (FOIT) while fonts load and negatively impacting Cumulative Layout Shift scores.', 'wpshadow' ),
				implode( ', ', $missing_display_files )
			),
			'severity'     => 'medium',
			'threat_level' => 40,
			'kb_link'      => 'https://wpshadow.com/kb/font-loading?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'affected_files' => $missing_display_files,
				'fix'            => __( 'Add "font-display: swap;" to all @font-face declarations in your theme CSS files. This tells browsers to render text with a fallback font immediately and swap it once the custom font has loaded. If using Google Fonts or external fonts, consider using the OMGF plugin to host them locally and apply font-display automatically.', 'wpshadow' ),
			),
		);
	}
}
