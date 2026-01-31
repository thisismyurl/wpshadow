<?php
/**
 * Polylang String Translations Diagnostic
 *
 * Polylang string translations missing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.307.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Polylang String Translations Diagnostic Class
 *
 * @since 1.307.0000
 */
class Diagnostic_PolylangStringTranslations extends Diagnostic_Base {

	protected static $slug = 'polylang-string-translations';
	protected static $title = 'Polylang String Translations';
	protected static $description = 'Polylang string translations missing';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'POLYLANG_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: String translations registered
		$strings = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}polylang_strings"
		);
		
		if ( $strings === null ) {
			return null; // Table doesn't exist
		}
		
		// Check 2: Untranslated strings
		$languages = get_option( 'polylang', array() );
		if ( isset( $languages['languages'] ) && count( $languages['languages'] ) > 0 ) {
			$untranslated = $wpdb->get_var(
				"SELECT COUNT(DISTINCT string_id) 
				FROM {$wpdb->prefix}polylang_strings ps
				LEFT JOIN {$wpdb->prefix}polylang_translations pt ON ps.string_id = pt.object_id
				WHERE pt.translation IS NULL OR pt.translation = ''"
			);
			
			if ( $untranslated > 10 ) {
				$issues[] = sprintf( __( '%d untranslated strings', 'wpshadow' ), $untranslated );
			}
		}
		
		// Check 3: Language switcher
		$switcher = get_option( 'polylang_switcher', array() );
		if ( empty( $switcher ) || ! isset( $switcher['show_names'] ) ) {
			$issues[] = __( 'Language switcher not configured', 'wpshadow' );
		}
		
		// Check 4: Media translation
		$media_support = get_option( 'polylang_media_support', 1 );
		if ( ! $media_support ) {
			$issues[] = __( 'Media translation disabled (untranslated images)', 'wpshadow' );
		}
		
		// Check 5: Default language fallback
		$force_lang = get_option( 'polylang_force_lang', 0 );
		if ( ! $force_lang ) {
			$issues[] = __( 'Language detection disabled (may show wrong language)', 'wpshadow' );
		}
		
		// Check 6: Translation synchronization
		$sync = get_option( 'polylang_sync', array() );
		if ( empty( $sync ) ) {
			$issues[] = __( 'Post synchronization disabled (manual updates needed)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of string translation issues */
				__( 'Polylang has %d string translation issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/polylang-string-translations',
		);
	}
}
