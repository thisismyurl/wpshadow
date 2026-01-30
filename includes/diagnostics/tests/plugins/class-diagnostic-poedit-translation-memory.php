<?php
/**
 * Poedit Translation Memory Diagnostic
 *
 * Poedit Translation Memory misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1172.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Poedit Translation Memory Diagnostic Class
 *
 * @since 1.1172.0000
 */
class Diagnostic_PoeditTranslationMemory extends Diagnostic_Base {

	protected static $slug = 'poedit-translation-memory';
	protected static $title = 'Poedit Translation Memory';
	protected static $description = 'Poedit Translation Memory misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		// Poedit is a desktop application, not a WordPress plugin.
		// This diagnostic checks for translation file issues that might indicate
		// problems with the translation workflow.
		
		$issues = array();
		
		// Check 1: Missing translation files
		$active_plugins = get_option( 'active_plugins', array() );
		$missing_translations = 0;
		
		foreach ( $active_plugins as $plugin ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin );
			$languages_dir = $plugin_dir . '/languages';
			
			if ( ! is_dir( $languages_dir ) ) {
				$missing_translations++;
			}
		}
		
		if ( $missing_translations > 5 ) {
			$issues[] = sprintf( __( '%d plugins without translation files', 'wpshadow' ), $missing_translations );
		}
		
		// Check 2: Outdated .pot files
		$theme_dir = get_template_directory();
		$theme_pot = $theme_dir . '/languages/' . get_template() . '.pot';
		
		if ( file_exists( $theme_pot ) ) {
			$pot_age = time() - filemtime( $theme_pot );
			if ( $pot_age > 31536000 ) { // 1 year
				$issues[] = sprintf( __( 'Theme .pot file %d days old (outdated strings)', 'wpshadow' ), $pot_age / 86400 );
			}
		}
		
		// Check 3: Translation memory not integrated
		// Check for Loco Translate which can use translation memory
		if ( ! defined( 'LOCO_VERSION' ) && ! class_exists( 'GlotPress' ) ) {
			$issues[] = __( 'No translation memory plugin (inconsistent translations)', 'wpshadow' );
		}
		
		// Check 4: Language packs not loading
		$locale = get_locale();
		if ( 'en_US' !== $locale ) {
			$wp_lang_file = WP_LANG_DIR . '/' . $locale . '.mo';
			if ( ! file_exists( $wp_lang_file ) ) {
				$issues[] = sprintf( __( 'WordPress language file missing for %s', 'wpshadow' ), $locale );
			}
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		} elseif ( count( $issues ) >= 2 ) {
			$threat_level = 53;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of translation issues */
				__( 'Translation workflow has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/poedit-translation-memory',
		);
	}
}
