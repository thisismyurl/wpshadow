<?php
/**
 * Theme CSS/JS Loading Errors Diagnostic
 *
 * Detects 404 errors or loading issues with theme assets.
 *
 * @package    WPShadow
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
 * Theme CSS/JS Loading Errors Diagnostic Class
 *
 * Checks for broken or missing theme assets.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Theme_CSS_JS_Loading_Errors extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-css-js-loading-errors';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme CSS/JS Loading Errors';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for broken theme assets';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts, $wp_styles;

		$theme = wp_get_theme();
		$theme_slug = get_stylesheet();
		$broken_assets = array();

		// Check enqueued scripts.
		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( isset( $script->src ) && is_string( $script->src ) && false !== strpos( $script->src, '/themes/' . $theme_slug ) ) {
					$file_path = str_replace( content_url(), WP_CONTENT_DIR, $script->src );
					$file_path = preg_replace( '/\?.*$/', '', $file_path ); // Remove query string.

					if ( is_string( $file_path ) && ! file_exists( $file_path ) ) {
						$broken_assets[] = array(
							'type'   => 'script',
							'handle' => $handle,
							'src'    => basename( $script->src ),
						);
					}
				}
			}
		}

		// Check enqueued styles.
		if ( isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( isset( $style->src ) && is_string( $style->src ) && false !== strpos( $style->src, '/themes/' . $theme_slug ) ) {
					$file_path = str_replace( content_url(), WP_CONTENT_DIR, $style->src );
					$file_path = preg_replace( '/\?.*$/', '', $file_path );

					if ( is_string( $file_path ) && ! file_exists( $file_path ) ) {
						$broken_assets[] = array(
							'type'   => 'style',
							'handle' => $handle,
							'src'    => basename( $style->src ),
						);
					}
				}
			}
		}

		if ( ! empty( $broken_assets ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of broken assets */
					_n(
						'%d theme asset file is missing or inaccessible',
						'%d theme asset files are missing or inaccessible',
						count( $broken_assets ),
						'wpshadow'
					),
					count( $broken_assets )
				),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'details'     => array(
					'theme'         => $theme->get( 'Name' ),
					'broken_assets' => $broken_assets,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-css-js-loading-errors?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
