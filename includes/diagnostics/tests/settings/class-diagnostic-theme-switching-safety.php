<?php
/**
 * Theme Switching Safety Diagnostic
 *
 * Validates that theme switching operations are safe and that the current
 * theme will not cause issues when switched away from.
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
 * Theme Switching Safety Diagnostic Class
 *
 * Checks theme switching safety and compatibility.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Theme_Switching_Safety extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-switching-safety';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Switching Safety';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates safe theme switching capability';

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

		// Get all available themes.
		$themes = wp_get_themes();

		if ( empty( $themes ) || count( $themes ) < 2 ) {
			$issues[] = __( 'Less than 2 themes available (cannot safely switch themes)', 'wpshadow' );
		}

		// Get active theme.
		$active_theme = wp_get_theme();
		$active_slug  = $active_theme->get_stylesheet();

		// Check if there's a fallback theme.
		$fallback_available = false;
		$fallback_theme     = null;

		foreach ( $themes as $theme_slug => $theme ) {
			if ( $theme_slug !== $active_slug ) {
				// Check if this theme is valid and can be switched to.
				if ( $theme->exists() && ! is_wp_error( $theme->errors() ) ) {
					if ( ! $fallback_available ) {
						$fallback_available = true;
						$fallback_theme     = $theme_slug;
					}
				}
			}
		}

		if ( ! $fallback_available ) {
			$issues[] = __( 'No valid alternative theme available for safe switching', 'wpshadow' );
		}

		// Check for theme-specific options that might cause issues.
		$theme_options = get_option( 'theme_mods_' . str_replace( '/', '_', $active_slug ) );

		if ( ! empty( $theme_options ) ) {
			// Theme has custom options - check if they're stored safely.
			// Some themes store critical functionality in options.
		}

		// Check for theme hooks that might interfere with switching.
		global $wp_filter;

		$theme_hooks = array();
		if ( isset( $wp_filter['wp_head'] ) ) {
			// Check for critical wp_head hooks from theme.
			$priority_hooks = (array) $wp_filter['wp_head'];
			foreach ( $priority_hooks as $priority => $callbacks ) {
				if ( is_array( $callbacks ) ) {
					foreach ( $callbacks as $callback_array ) {
						if ( isset( $callback_array['function'] ) ) {
							$func = $callback_array['function'];
							if ( is_string( $func ) && false !== stripos( $func, 'theme' ) ) {
								$theme_hooks[] = $func;
							}
						}
					}
				}
			}
		}

		// Check for theme shortcodes.
		global $shortcode_tags;
		$theme_shortcodes = array();

		if ( ! empty( $shortcode_tags ) ) {
			foreach ( $shortcode_tags as $shortcode => $callback ) {
				// Check if shortcode is defined in theme.
				if ( is_array( $callback ) && is_object( $callback[0] ) ) {
					$class_name = get_class( $callback[0] );
					if ( false !== stripos( $class_name, 'theme' ) ) {
						$theme_shortcodes[] = $shortcode;
					}
				}
			}
		}

		if ( ! empty( $theme_shortcodes ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of theme shortcodes */
				__( 'Theme defines %d custom shortcodes (posts may break if theme is switched)', 'wpshadow' ),
				count( $theme_shortcodes )
			);
		}

		// Check for theme-specific custom post types.
		$custom_post_types = array();
		$post_types        = get_post_types( array( 'public' => true ) );

		foreach ( $post_types as $post_type ) {
			$post_type_obj = get_post_type_object( $post_type );
			if ( $post_type_obj && ! in_array( $post_type, array( 'post', 'page', 'attachment' ), true ) ) {
				$custom_post_types[] = $post_type;
			}
		}

		if ( ! empty( $custom_post_types ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of custom post types */
				__( '%d custom post types exist (may not be properly rendered in other themes)', 'wpshadow' ),
				count( $custom_post_types )
			);
		}

		// Check for CSS classes that might not exist in other themes.
		$template_dir = get_template_directory();

		// Check for theme-specific CSS.
		$css_dir = $template_dir . '/css';
		if ( is_dir( $css_dir ) ) {
			$css_files = glob( $css_dir . '/*.css' );
			if ( count( $css_files ) > 5 ) {
				$issues[] = sprintf(
					/* translators: %d: number of CSS files */
					__( 'Theme has %d CSS files (switching themes may break styling)', 'wpshadow' ),
					count( $css_files )
				);
			}
		}

		// Check if there are posts using theme-specific features.
		global $wpdb;

		// Check for posts with theme shortcodes.
		if ( ! empty( $theme_shortcodes ) ) {
			foreach ( $theme_shortcodes as $shortcode ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$posts_with_shortcode = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE %s",
						'%[' . $shortcode . '%'
					)
				);

				if ( $posts_with_shortcode > 0 ) {
					$issues[] = sprintf(
						/* translators: 1: shortcode, 2: post count */
						__( '%2$d posts use theme shortcode: [%1$s] (will break if theme is switched)', 'wpshadow' ),
						$shortcode,
						$posts_with_shortcode
					);
				}
			}
		}

		// Check theme's compatibility with WordPress version.
		$requires_at_least = $active_theme->get( 'RequiresWP' );
		$requires_php      = $active_theme->get( 'RequiresPHP' );

		if ( $requires_at_least && version_compare( get_bloginfo( 'version' ), $requires_at_least, '<' ) ) {
			$issues[] = sprintf(
				/* translators: 1: required version, 2: current version */
				__( 'Theme requires WordPress %1$s (currently %2$s)', 'wpshadow' ),
				$requires_at_least,
				get_bloginfo( 'version' )
			);
		}

		if ( $requires_php && version_compare( PHP_VERSION, $requires_php, '<' ) ) {
			$issues[] = sprintf(
				/* translators: 1: required version, 2: current version */
				__( 'Theme requires PHP %1$s (currently %2$s)', 'wpshadow' ),
				$requires_php,
				PHP_VERSION
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of theme switching issues */
					__( 'Found %d theme switching safety concerns.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'details'      => array(
					'issues'              => $issues,
					'active_theme'        => $active_slug,
					'fallback_available'  => $fallback_available,
					'fallback_theme'      => $fallback_theme,
					'custom_post_types'   => $custom_post_types,
					'recommendation'      => __( 'Test theme switching before deploying to production. Keep at least one alternative theme available. Avoid theme-specific shortcodes and custom post types.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
