<?php
/**
 * Language Switcher Implementation Diagnostic
 *
 * Tests if site has proper language switching functionality for multilingual users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7034.1410
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Language Switcher Implementation Diagnostic Class
 *
 * Validates that multilingual sites have proper language switcher
 * implementation for easy language selection.
 *
 * @since 1.7034.1410
 */
class Diagnostic_Language_Switcher_Implementation extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'language-switcher-implementation';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Language Switcher Implementation';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site has proper language switching functionality for multilingual users';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests language switcher implementation including plugin detection,
	 * switcher placement, and accessibility.
	 *
	 * @since  1.7034.1410
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check for multilingual plugins.
		$multilingual_plugins = array(
			'polylang/polylang.php'                    => array( 'name' => 'Polylang', 'has_switcher' => true ),
			'sitepress-multilingual-cms/sitepress.php' => array( 'name' => 'WPML', 'has_switcher' => true ),
			'translatepress-multilingual/index.php'    => array( 'name' => 'TranslatePress', 'has_switcher' => true ),
			'weglot/weglot.php'                        => array( 'name' => 'Weglot', 'has_switcher' => true ),
			'qtranslate-x/qtranslate.php'              => array( 'name' => 'qTranslate-X', 'has_switcher' => true ),
		);

		$active_multilingual_plugin = null;
		$has_builtin_switcher = false;

		foreach ( $multilingual_plugins as $plugin => $data ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_multilingual_plugin = $data['name'];
				$has_builtin_switcher = $data['has_switcher'];
				break;
			}
		}

		// If no multilingual plugin, check if site is English-only.
		$site_language = get_locale();
		$is_english_only = ( $site_language === 'en_US' );

		// If English-only and no multilingual plugin, no issue.
		if ( $is_english_only && ! $active_multilingual_plugin ) {
			return null;
		}

		// Check for language switcher in navigation menus.
		$nav_menus = wp_get_nav_menus();
		$has_language_menu_item = false;

		foreach ( $nav_menus as $menu ) {
			$menu_items = wp_get_nav_menu_items( $menu->term_id );
			if ( is_array( $menu_items ) ) {
				foreach ( $menu_items as $item ) {
					$classes = is_array( $item->classes ) ? $item->classes : array();
					if ( in_array( 'lang-item', $classes, true ) ||
						 in_array( 'language-switcher', $classes, true ) ||
						 strpos( $item->title, 'Language' ) !== false ) {
						$has_language_menu_item = true;
						break 2;
					}
				}
			}
		}

		// Check header.php for language switcher.
		$header_file = get_template_directory() . '/header.php';
		$header_has_switcher = false;

		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			$header_has_switcher = ( strpos( $header_content, 'language-switcher' ) !== false ) ||
								 ( strpos( $header_content, 'pll_the_languages' ) !== false ) ||
								 ( strpos( $header_content, 'icl_get_languages' ) !== false ) ||
								 ( strpos( $header_content, 'trp_language_switcher' ) !== false );
		}

		// Check widgets for language switcher.
		$sidebars = wp_get_sidebars_widgets();
		$has_language_widget = false;

		foreach ( $sidebars as $sidebar => $widgets ) {
			if ( is_array( $widgets ) ) {
				foreach ( $widgets as $widget ) {
					if ( strpos( $widget, 'polylang' ) !== false ||
						 strpos( $widget, 'wpml' ) !== false ||
						 strpos( $widget, 'translatepress' ) !== false ) {
						$has_language_widget = true;
						break 2;
					}
				}
			}
		}

		// Check for custom language switcher CSS.
		$style_css = get_stylesheet_directory() . '/style.css';
		$has_switcher_styles = false;

		if ( file_exists( $style_css ) ) {
			$style_content = file_get_contents( $style_css );
			$has_switcher_styles = ( strpos( $style_content, 'language-switcher' ) !== false ) ||
								 ( strpos( $style_content, '.lang-item' ) !== false );
		}

		// Check switcher accessibility.
		$switcher_accessible = false;
		if ( $header_has_switcher && file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			$switcher_accessible = ( strpos( $header_content, 'aria-label' ) !== false ) ||
								 ( strpos( $header_content, '<nav' ) !== false );
		}

		// Count available languages.
		$languages_count = 1; // Default to 1 (site language).
		if ( function_exists( 'pll_languages_list' ) ) {
			$languages = pll_languages_list();
			$languages_count = is_array( $languages ) ? count( $languages ) : 1;
		} elseif ( function_exists( 'icl_get_languages' ) ) {
			$languages = icl_get_languages();
			$languages_count = is_array( $languages ) ? count( $languages ) : 1;
		}

		// Check for issues.
		$issues = array();

		// Issue 1: Multilingual plugin but no visible switcher.
		if ( $active_multilingual_plugin && ! $header_has_switcher && ! $has_language_widget && ! $has_language_menu_item ) {
			$issues[] = array(
				'type'        => 'no_visible_switcher',
				'description' => sprintf(
					/* translators: %s: plugin name */
					__( '%s is active but no language switcher found in header, menu, or widgets', 'wpshadow' ),
					$active_multilingual_plugin
				),
			);
		}

		// Issue 2: Multiple languages but no switcher.
		if ( $languages_count > 1 && ! $header_has_switcher && ! $has_language_widget ) {
			$issues[] = array(
				'type'        => 'multiple_languages_no_switcher',
				'description' => sprintf(
					/* translators: %d: number of languages */
					__( 'Site has %d languages configured but no user-accessible switcher', 'wpshadow' ),
					$languages_count
				),
			);
		}

		// Issue 3: Switcher exists but not accessible.
		if ( $header_has_switcher && ! $switcher_accessible ) {
			$issues[] = array(
				'type'        => 'switcher_not_accessible',
				'description' => __( 'Language switcher lacks ARIA labels or semantic markup; not accessible to screen readers', 'wpshadow' ),
			);
		}

		// Issue 4: Switcher not styled.
		if ( ( $header_has_switcher || $has_language_widget ) && ! $has_switcher_styles ) {
			$issues[] = array(
				'type'        => 'switcher_not_styled',
				'description' => __( 'Language switcher has no custom styling; may not match site design', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Language switcher is missing or improperly implemented, preventing users from accessing translated content', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/language-switcher-implementation',
				'details'      => array(
					'active_multilingual_plugin' => $active_multilingual_plugin,
					'has_builtin_switcher'    => $has_builtin_switcher,
					'header_has_switcher'     => $header_has_switcher,
					'has_language_menu_item'  => $has_language_menu_item,
					'has_language_widget'     => $has_language_widget,
					'has_switcher_styles'     => $has_switcher_styles,
					'switcher_accessible'     => $switcher_accessible,
					'languages_count'         => $languages_count,
					'site_language'           => $site_language,
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Add language switcher to header, use ARIA labels, style to match site design', 'wpshadow' ),
					'switcher_implementations' => array(
						'Polylang'        => 'pll_the_languages()',
						'WPML'            => 'wpml_add_language_selector()',
						'TranslatePress'  => '[language-switcher]',
						'Weglot'          => 'Auto-injected by plugin',
					),
					'switcher_best_practices' => array(
						'Placement'       => 'Header (top-right) or footer',
						'Format'          => 'Flag icons + language names',
						'Mobile'          => 'Dropdown or collapsible menu',
						'Accessibility'   => 'ARIA labels and semantic nav',
						'Current language' => 'Visually distinct from others',
					),
					'accessible_switcher_code' => array(
						'Wrapper' => '<nav aria-label="Language switcher">',
						'List'    => '<ul class="language-switcher">',
						'Current' => '<li><a aria-current="page">English</a></li>',
						'Other'   => '<li><a href="..." hreflang="es">Español</a></li>',
					),
					'seo_benefit'             => 'Language switcher helps Google discover translated versions (hreflang)',
				),
			);
		}

		return null;
	}
}
