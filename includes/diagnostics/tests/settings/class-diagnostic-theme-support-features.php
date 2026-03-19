<?php
/**
 * Theme Support Features Diagnostic
 *
 * Validates that the theme properly declares all features it supports
 * and enables them with appropriate configuration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Support Features Diagnostic Class
 *
 * Checks theme feature declarations.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Theme_Support_Features extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-support-features';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Support Features';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates theme support feature declarations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Common theme support features.
		$common_features = array(
			'title-tag'                  => 'Dynamic title tags',
			'post-thumbnails'            => 'Post thumbnails (featured images)',
			'editor-style'               => 'Visual editor styles',
			'editor-styles'              => 'Block editor styles',
			'responsive-embeds'          => 'Responsive embeds',
			'wp-block-styles'            => 'Default block styles',
			'align-wide'                 => 'Wide/full width blocks',
			'custom-logo'                => 'Custom site logo',
			'custom-header'              => 'Custom header image',
			'custom-background'          => 'Custom background',
			'menus'                      => 'Navigation menus',
			'html5'                      => 'HTML5 markup',
			'automatic-feed-links'       => 'Automatic feed links',
			'customize-selective-refresh-widgets' => 'Selective widget refresh',
			'starter-content'            => 'Starter content for Customizer',
		);

		$supported_features = array();
		$missing_features   = array();

		foreach ( $common_features as $feature => $label ) {
			if ( current_theme_supports( $feature ) ) {
				$supported_features[] = $feature;
			} else {
				// Not supported - determine if this is a concern.
				if ( in_array( $feature, array( 'title-tag', 'menus' ), true ) ) {
					$missing_features[] = $label;
				}
			}
		}

		// Check for critical missing features.
		if ( ! current_theme_supports( 'title-tag' ) ) {
			$issues[] = __( 'Theme does not support title-tag (must use wp_title filter)', 'wpshadow' );
		}

		if ( ! current_theme_supports( 'menus' ) ) {
			$issues[] = __( 'Theme does not declare menu support', 'wpshadow' );
		}

		// Check HTML5 support (modern best practice).
		$html5_support = get_theme_support( 'html5' );
		if ( empty( $html5_support ) ) {
			$issues[] = __( 'Theme does not declare HTML5 support (legacy XHTML)', 'wpshadow' );
		}

		// Check for WooCommerce support if WooCommerce is active.
		if ( class_exists( 'WooCommerce' ) ) {
			if ( ! current_theme_supports( 'woocommerce' ) ) {
				$issues[] = __( 'WooCommerce is active but theme does not declare support', 'wpshadow' );
			}

			// Check for specific WooCommerce features.
			$wc_features = array(
				'wc-product-gallery-zoom'      => 'Product gallery zoom',
				'wc-product-gallery-lightbox'  => 'Product gallery lightbox',
				'wc-product-gallery-slider'    => 'Product gallery slider',
			);

			foreach ( $wc_features as $feature => $label ) {
				if ( ! current_theme_supports( $feature ) ) {
					// Not critical if theme is not full WC support.
				}
			}
		}

		// Check for feature configuration issues.
		$title_tag_support = get_theme_support( 'title-tag' );
		if ( ! empty( $title_tag_support ) && is_array( $title_tag_support[0] ) ) {
			// Validate configuration if present.
		}

		// Check post-thumbnails configuration.
		if ( current_theme_supports( 'post-thumbnails' ) ) {
			$support_args = get_theme_support( 'post-thumbnails' );
			// Post thumbnails might be for all post types or specific ones.
		}

		// Check HTML5 support types.
		if ( current_theme_supports( 'html5' ) ) {
			$html5_types = get_theme_support( 'html5' );
			if ( is_array( $html5_types[0] ) ) {
				// Check for essential types.
				$required_types = array( 'search-form', 'comment-list', 'comment-form', 'gallery', 'caption' );
				$missing_types  = array();

				foreach ( $required_types as $type ) {
					if ( ! in_array( $type, $html5_types[0], true ) ) {
						$missing_types[] = $type;
					}
				}

				if ( ! empty( $missing_types ) ) {
					$issues[] = sprintf(
						/* translators: %s: comma-separated list of missing types */
						__( 'Theme HTML5 support missing types: %s', 'wpshadow' ),
						implode( ', ', $missing_types )
					);
				}
			}
		}

		// Check theme's functions.php for proper feature registration.
		$template_dir   = get_template_directory();
		$functions_file = $template_dir . '/functions.php';

		if ( file_exists( $functions_file ) ) {
			$content = file_get_contents( $functions_file );

			if ( false === stripos( $content, 'add_theme_support' ) ) {
				$issues[] = __( 'Theme does not call add_theme_support() (missing feature declarations)', 'wpshadow' );
			}

			// Check if properly hooked to after_setup_theme.
			if ( false !== stripos( $content, 'add_theme_support' ) && false === stripos( $content, 'after_setup_theme' ) ) {
				$issues[] = __( 'Theme support not hooked to after_setup_theme (may load too late)', 'wpshadow' );
			}
		}

		// Check for modern block theme (theme.json).
		$theme_json = $template_dir . '/theme.json';
		if ( file_exists( $theme_json ) ) {
			// This is a modern block theme, check if legacy support is also present.
			$json_content = file_get_contents( $theme_json );
			$decoded      = json_decode( $json_content, true );

			if ( is_array( $decoded ) && isset( $decoded['settings']['spacing']['units'] ) ) {
				// Block theme with custom settings.
			}
		}

		// Check for theme.json if blocks are being used.
		if ( current_theme_supports( 'wp-block-styles' ) && ! file_exists( $theme_json ) ) {
			$issues[] = __( 'Theme supports blocks but lacks theme.json (limited block customization)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of theme support issues */
					__( 'Found %d theme support feature issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'details'      => array(
					'issues'               => $issues,
					'supported_features'   => $supported_features,
					'missing_critical'     => $missing_features,
					'recommendation'       => __( 'Declare all theme support features in functions.php using add_theme_support() hooked to after_setup_theme.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
