<?php
/**
 * Theme CSS/JS Loading Errors Treatment
 *
 * Detects 404 errors or loading issues with theme assets.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.5049.1245
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme CSS/JS Loading Errors Treatment Class
 *
 * Checks for broken or missing theme assets.
 *
 * @since 1.5049.1245
 */
class Treatment_Theme_CSS_JS_Loading_Errors extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-css-js-loading-errors';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme CSS/JS Loading Errors';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for broken theme assets';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.5049.1245
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
				if ( isset( $script->src ) && strpos( $script->src, '/themes/' . $theme_slug ) !== false ) {
					$file_path = str_replace( content_url(), WP_CONTENT_DIR, $script->src );
					$file_path = preg_replace( '/\?.*$/', '', $file_path ); // Remove query string.

					if ( ! file_exists( $file_path ) ) {
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
				if ( isset( $style->src ) && is_string( $style->src ) && strpos( $style->src, '/themes/' . $theme_slug ) !== false ) {
					$file_path = str_replace( content_url(), WP_CONTENT_DIR, $style->src );
					$file_path = preg_replace( '/\?.*$/', '', $file_path );

					if ( ! file_exists( $file_path ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/theme-css-js-loading-errors',
			);
		}

		return null;
	}
}
