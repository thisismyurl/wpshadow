<?php
/**
 * Language Switcher Visibility Diagnostic
 *
 * Detects missing or hidden language switcher on multilingual sites.
 * Users can't change language if switcher is not visible.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1745
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Language_Switcher_Visibility Class
 *
 * Checks if language switcher widget/menu is visible on multilingual sites.
 *
 * @since 1.6028.1745
 */
class Diagnostic_Language_Switcher_Visibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'language-switcher-visibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Language Switcher Not Visible';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if language switcher is accessible on multilingual sites';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1745
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only check if multilingual plugin is active.
		$multilingual_data = self::detect_multilingual_setup();

		if ( ! $multilingual_data['is_multilingual'] ) {
			return null; // Not a multilingual site.
		}

		if ( $multilingual_data['language_count'] < 2 ) {
			return null; // Only one language configured.
		}

		// Check if switcher is visible.
		$switcher_visible = self::is_switcher_visible( $multilingual_data['plugin'] );

		if ( $switcher_visible ) {
			return null; // Switcher is properly configured.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: plugin name, 2: language count */
				__( '%1$s detected with %2$d languages but no visible language switcher', 'wpshadow' ),
				$multilingual_data['plugin_name'],
				$multilingual_data['language_count']
			),
			'severity'    => 'low',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/language-switcher',
			'family'      => self::$family,
			'meta'        => array(
				'plugin'            => $multilingual_data['plugin'],
				'plugin_name'       => $multilingual_data['plugin_name'],
				'language_count'    => $multilingual_data['language_count'],
				'languages'         => $multilingual_data['languages'],
				'recommended'       => __( 'Add language switcher to header, footer, or sidebar', 'wpshadow' ),
				'impact_level'      => 'medium',
				'immediate_actions' => array(
					__( 'Add language switcher widget', 'wpshadow' ),
					__( 'Or add switcher to navigation menu', 'wpshadow' ),
					__( 'Test visibility on mobile', 'wpshadow' ),
					__( 'Ensure clear language labels', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Multilingual sites need visible language switcher so users can find content in their language. Without it, visitors may not realize translations exist. This reduces engagement, increases bounce rate, and makes international investment worthless. The switcher should be prominent and recognizable.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Users Stuck in Wrong Language: Can\'t find their language', 'wpshadow' ),
					__( 'Higher Bounce Rate: Leave if content isn\'t readable', 'wpshadow' ),
					__( 'Lost International Traffic: Translations go unused', 'wpshadow' ),
					__( 'Poor User Experience: Frustration finding content', 'wpshadow' ),
				),
				'detection'     => array(
					'plugin'        => $multilingual_data['plugin_name'],
					'languages'     => $multilingual_data['languages'],
					'widget_areas'  => self::get_widget_areas(),
					'nav_menus'     => self::get_navigation_menus(),
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Add Widget to Sidebar', 'wpshadow' ),
						'description' => __( 'Use built-in language switcher widget', 'wpshadow' ),
						'steps'       => self::get_widget_steps( $multilingual_data['plugin'] ),
					),
					'premium'  => array(
						'label'       => __( 'Add to Navigation Menu', 'wpshadow' ),
						'description' => __( 'Display switcher in header menu', 'wpshadow' ),
						'steps'       => self::get_menu_steps( $multilingual_data['plugin'] ),
					),
					'advanced' => array(
						'label'       => __( 'Custom Shortcode Placement', 'wpshadow' ),
						'description' => __( 'Add switcher anywhere with shortcode', 'wpshadow' ),
						'steps'       => self::get_shortcode_steps( $multilingual_data['plugin'] ),
					),
				),
				'best_practices' => array(
					__( 'Place switcher in header or top navigation', 'wpshadow' ),
					__( 'Use flags + language names (not just flags)', 'wpshadow' ),
					__( 'Make visible on mobile devices', 'wpshadow' ),
					__( 'Show current language clearly', 'wpshadow' ),
					__( 'Keep in same position across all pages', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Visit site homepage in logged-out mode', 'wpshadow' ),
						__( 'Look for language switcher in header/footer', 'wpshadow' ),
						__( 'Test on mobile device (often hidden)', 'wpshadow' ),
						__( 'Click switcher to verify it works', 'wpshadow' ),
					),
					'expected_result' => __( 'Language switcher visible and functional on all pages', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Detect multilingual plugin and configuration.
	 *
	 * @since  1.6028.1745
	 * @return array Multilingual setup details.
	 */
	private static function detect_multilingual_setup() {
		$data = array(
			'is_multilingual' => false,
			'plugin'          => '',
			'plugin_name'     => '',
			'language_count'  => 0,
			'languages'       => array(),
		);

		// Check WPML.
		if ( defined( 'ICL_SITEPRESS_VERSION' ) && function_exists( 'icl_get_languages' ) ) {
			$languages = icl_get_languages( 'skip_missing=0' );
			$data['is_multilingual'] = true;
			$data['plugin']          = 'wpml';
			$data['plugin_name']     = 'WPML';
			$data['language_count']  = count( $languages );
			$data['languages']       = array_column( $languages, 'native_name' );
		}

		// Check Polylang.
		if ( function_exists( 'pll_languages_list' ) ) {
			$languages = pll_languages_list( array( 'fields' => 'name' ) );
			$data['is_multilingual'] = true;
			$data['plugin']          = 'polylang';
			$data['plugin_name']     = 'Polylang';
			$data['language_count']  = count( $languages );
			$data['languages']       = $languages;
		}

		// Check TranslatePress.
		if ( class_exists( 'TRP_Translate_Press' ) && function_exists( 'trp_get_languages' ) ) {
			$trp_settings = get_option( 'trp_settings' );
			$languages = ! empty( $trp_settings['publish-languages'] ) ? $trp_settings['publish-languages'] : array();
			$data['is_multilingual'] = true;
			$data['plugin']          = 'translatepress';
			$data['plugin_name']     = 'TranslatePress';
			$data['language_count']  = count( $languages );
			$data['languages']       = $languages;
		}

		return $data;
	}

	/**
	 * Check if language switcher is visible.
	 *
	 * @since  1.6028.1745
	 * @param  string $plugin Multilingual plugin slug.
	 * @return bool True if switcher found.
	 */
	private static function is_switcher_visible( $plugin ) {
		// Check for widgets.
		$widget_found = self::check_widgets_for_switcher( $plugin );
		if ( $widget_found ) {
			return true;
		}

		// Check for menu items.
		$menu_found = self::check_menus_for_switcher( $plugin );
		if ( $menu_found ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if switcher widget is in any sidebar.
	 *
	 * @since  1.6028.1745
	 * @param  string $plugin Multilingual plugin slug.
	 * @return bool True if widget found.
	 */
	private static function check_widgets_for_switcher( $plugin ) {
		$sidebars_widgets = wp_get_sidebars_widgets();

		if ( empty( $sidebars_widgets ) ) {
			return false;
		}

		$widget_ids = array(
			'wpml'           => 'icl_lang_sel_widget',
			'polylang'       => 'polylang',
			'translatepress' => 'trp_language_switcher',
		);

		$target_widget = $widget_ids[ $plugin ] ?? '';

		foreach ( $sidebars_widgets as $sidebar => $widgets ) {
			if ( $sidebar === 'wp_inactive_widgets' ) {
				continue;
			}

			foreach ( $widgets as $widget ) {
				if ( strpos( $widget, $target_widget ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if switcher is in navigation menu.
	 *
	 * @since  1.6028.1745
	 * @param  string $plugin Multilingual plugin slug.
	 * @return bool True if menu item found.
	 */
	private static function check_menus_for_switcher( $plugin ) {
		$menus = wp_get_nav_menus();

		foreach ( $menus as $menu ) {
			$menu_items = wp_get_nav_menu_items( $menu->term_id );

			if ( empty( $menu_items ) ) {
				continue;
			}

			foreach ( $menu_items as $item ) {
				// Check for language switcher menu items.
				if ( $plugin === 'wpml' && strpos( $item->url, '#wpml' ) !== false ) {
					return true;
				}
				if ( $plugin === 'polylang' && $item->type === 'lang_switcher' ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get registered widget areas.
	 *
	 * @since  1.6028.1745
	 * @return array Widget area names.
	 */
	private static function get_widget_areas() {
		global $wp_registered_sidebars;
		return array_column( $wp_registered_sidebars, 'name' );
	}

	/**
	 * Get registered navigation menus.
	 *
	 * @since  1.6028.1745
	 * @return array Menu names.
	 */
	private static function get_navigation_menus() {
		$menus = wp_get_nav_menus();
		return array_column( $menus, 'name' );
	}

	/**
	 * Get widget setup steps for specific plugin.
	 *
	 * @since  1.6028.1745
	 * @param  string $plugin Plugin slug.
	 * @return array Setup steps.
	 */
	private static function get_widget_steps( $plugin ) {
		$steps = array(
			'wpml'           => array(
				__( 'Go to Appearance → Widgets', 'wpshadow' ),
				__( 'Find "WPML Language Switcher" widget', 'wpshadow' ),
				__( 'Drag to Header or Footer widget area', 'wpshadow' ),
				__( 'Configure display options (flags, names)', 'wpshadow' ),
				__( 'Save and view site to verify', 'wpshadow' ),
			),
			'polylang'       => array(
				__( 'Go to Appearance → Widgets', 'wpshadow' ),
				__( 'Find "Language Switcher" widget', 'wpshadow' ),
				__( 'Drag to Header or Sidebar', 'wpshadow' ),
				__( 'Choose dropdown or list display', 'wpshadow' ),
				__( 'Save and test on frontend', 'wpshadow' ),
			),
			'translatepress' => array(
				__( 'Go to Appearance → Widgets', 'wpshadow' ),
				__( 'Find "Language Switcher" widget', 'wpshadow' ),
				__( 'Add to Header widget area', 'wpshadow' ),
				__( 'Configure flags and labels', 'wpshadow' ),
				__( 'Verify visibility on site', 'wpshadow' ),
			),
		);

		return $steps[ $plugin ] ?? array( __( 'Check plugin documentation for widget setup', 'wpshadow' ) );
	}

	/**
	 * Get menu setup steps for specific plugin.
	 *
	 * @since  1.6028.1745
	 * @param  string $plugin Plugin slug.
	 * @return array Setup steps.
	 */
	private static function get_menu_steps( $plugin ) {
		$steps = array(
			'wpml'           => array(
				__( 'Go to Appearance → Menus', 'wpshadow' ),
				__( 'Enable "Language switcher" in Screen Options', 'wpshadow' ),
				__( 'Add "Language switcher" to menu', 'wpshadow' ),
				__( 'Position at end of primary menu', 'wpshadow' ),
				__( 'Save menu and test', 'wpshadow' ),
			),
			'polylang'       => array(
				__( 'Go to Appearance → Menus', 'wpshadow' ),
				__( 'Enable "Language Switcher" in Screen Options', 'wpshadow' ),
				__( 'Add Language Switcher to menu', 'wpshadow' ),
				__( 'Configure as dropdown or list', 'wpshadow' ),
				__( 'Save and verify on site', 'wpshadow' ),
			),
			'translatepress' => array(
				__( 'Go to Settings → TranslatePress', 'wpshadow' ),
				__( 'Under "Language Switcher" tab', 'wpshadow' ),
				__( 'Enable "Add shortcode to menu"', 'wpshadow' ),
				__( 'Select target menu', 'wpshadow' ),
				__( 'Save settings', 'wpshadow' ),
			),
		);

		return $steps[ $plugin ] ?? array( __( 'Check plugin documentation for menu setup', 'wpshadow' ) );
	}

	/**
	 * Get shortcode setup steps for specific plugin.
	 *
	 * @since  1.6028.1745
	 * @param  string $plugin Plugin slug.
	 * @return array Setup steps.
	 */
	private static function get_shortcode_steps( $plugin ) {
		$shortcodes = array(
			'wpml'           => '[wpml_language_selector_widget]',
			'polylang'       => '[polylang_langswitcher]',
			'translatepress' => '[language-switcher]',
		);

		$shortcode = $shortcodes[ $plugin ] ?? '[language_switcher]';

		return array(
			sprintf(
				/* translators: %s: shortcode */
				__( 'Copy shortcode: %s', 'wpshadow' ),
				$shortcode
			),
			__( 'Go to Appearance → Widgets or page editor', 'wpshadow' ),
			__( 'Add "Shortcode" widget or block', 'wpshadow' ),
			__( 'Paste shortcode into widget', 'wpshadow' ),
			__( 'Save and verify on frontend', 'wpshadow' ),
		);
	}
}
