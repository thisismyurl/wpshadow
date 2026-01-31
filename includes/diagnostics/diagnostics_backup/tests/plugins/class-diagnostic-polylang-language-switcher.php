<?php
/**
 * Polylang Language Switcher Diagnostic
 *
 * Polylang language switcher not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.305.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Polylang Language Switcher Diagnostic Class
 *
 * @since 1.305.0000
 */
class Diagnostic_PolylangLanguageSwitcher extends Diagnostic_Base {

	protected static $slug = 'polylang-language-switcher';
	protected static $title = 'Polylang Language Switcher';
	protected static $description = 'Polylang language switcher not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'POLYLANG_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check if language switcher is enabled
		$switcher_enabled = get_option( 'polylang_switcher_enabled', '1' );
		if ( '0' === $switcher_enabled ) {
			$issues[] = 'language switcher disabled';
		}

		// Check for widget/menu location
		$active_widgets = get_option( 'sidebars_widgets', array() );
		$has_switcher_widget = false;
		foreach ( $active_widgets as $sidebar => $widgets ) {
			if ( is_array( $widgets ) ) {
				foreach ( $widgets as $widget ) {
					if ( false !== strpos( $widget, 'polylang' ) ) {
						$has_switcher_widget = true;
						break 2;
					}
				}
			}
		}

		if ( ! $has_switcher_widget && '1' === $switcher_enabled ) {
			$issues[] = 'language switcher enabled but not placed in any widget area';
		}

		// Check for flag display
		$show_flags = get_option( 'polylang_show_flags', '1' );
		if ( '0' === $show_flags ) {
			$issues[] = 'flags hidden (users may not recognize language options)';
		}

		// Check for dropdown vs list display
		$dropdown = get_option( 'polylang_force_dropdown', '0' );
		if ( '0' === $dropdown ) {
			global $wpdb;
			$lang_count = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->prefix}term_taxonomy WHERE taxonomy = 'language'"
			);

			if ( $lang_count > 5 ) {
				$issues[] = "many languages ({$lang_count}) displayed as list (use dropdown)";
			}
		}

		// Check for hide current language option
		$hide_current = get_option( 'polylang_hide_current', '0' );
		if ( '1' === $hide_current ) {
			$issues[] = 'current language hidden from switcher (confuses users)';
		}

		// Check for URL modification
		$url_modify = get_option( 'polylang_rewrite', '1' );
		if ( '0' === $url_modify ) {
			$issues[] = 'URL rewriting disabled (poor SEO for multilingual content)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 35 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Polylang language switcher configuration issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/polylang-language-switcher',
			);
		}

		return null;
	}
}
