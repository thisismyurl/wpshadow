<?php
/**
 * Theme Gutenberg Block Support Diagnostic
 *
 * Detects issues with theme's Gutenberg block editor support.
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
 * Theme Gutenberg Block Support Diagnostic Class
 *
 * Checks if theme properly supports Gutenberg block editor features.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Theme_Gutenberg_Block_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-gutenberg-block-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Gutenberg Block Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for theme Gutenberg compatibility';

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
		$theme = wp_get_theme();
		$issues = array();

		// Check for editor-style.css.
		$editor_style = locate_template( 'editor-style.css' );
		if ( empty( $editor_style ) ) {
			$issues[] = __( 'Theme missing editor-style.css', 'wpshadow' );
		}

		// Check if theme adds editor styles.
		if ( ! current_theme_supports( 'editor-styles' ) ) {
			$issues[] = __( 'Theme does not declare editor-styles support', 'wpshadow' );
		}

		// Check for block editor support features.
		$editor_features = array(
			'align-wide'              => __( 'Wide alignment support', 'wpshadow' ),
			'custom-spacing'          => __( 'Custom spacing support', 'wpshadow' ),
			'custom-line-height'      => __( 'Custom line height support', 'wpshadow' ),
			'editor-color-palette'    => __( 'Editor color palette', 'wpshadow' ),
			'editor-font-sizes'       => __( 'Editor font sizes', 'wpshadow' ),
			'responsive-embeds'       => __( 'Responsive embeds', 'wpshadow' ),
		);

		$missing_features = array();
		foreach ( $editor_features as $feature => $label ) {
			if ( ! current_theme_supports( $feature ) ) {
				$missing_features[] = $label;
			}
		}

		if ( count( $missing_features ) > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of missing features */
				_n(
					'%d Gutenberg feature not supported',
					'%d Gutenberg features not supported',
					count( $missing_features ),
					'wpshadow'
				),
				count( $missing_features )
			);
		}

		// Check for theme.json (WordPress 5.8+).
		$theme_json = locate_template( 'theme.json' );
		if ( empty( $theme_json ) && version_compare( get_bloginfo( 'version' ), '5.8', '>=' ) ) {
			$issues[] = __( 'Theme missing theme.json (recommended for modern block themes)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Theme may have incomplete Gutenberg block editor support', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'details'     => array(
					'theme'            => $theme->get( 'Name' ),
					'missing_features' => $missing_features,
					'issues'           => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-gutenberg-block-support?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
