<?php
/**
 * Qtranslate X Url Structure Diagnostic
 *
 * Qtranslate X Url Structure misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1178.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Qtranslate X Url Structure Diagnostic Class
 *
 * @since 1.1178.0000
 */
class Diagnostic_QtranslateXUrlStructure extends Diagnostic_Base {

	protected static $slug = 'qtranslate-x-url-structure';
	protected static $title = 'Qtranslate X Url Structure';
	protected static $description = 'Qtranslate X Url Structure misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! get_option( 'qtranslate_enabled', '' ) && ! get_option( 'qt_installed_language_keys', '' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: URL structure type selected
		$url_structure = get_option( 'qtranslate_url_structure', '' );
		if ( empty( $url_structure ) ) {
			$issues[] = 'URL structure type not configured';
		}

		// Check 2: Language code format
		$lang_format = get_option( 'qtranslate_language_code_format', '' );
		if ( empty( $lang_format ) ) {
			$issues[] = 'Language code format not set';
		}

		// Check 3: Default language configured
		$default_lang = get_option( 'qtranslate_default_language', '' );
		if ( empty( $default_lang ) ) {
			$issues[] = 'Default language not configured';
		}

		// Check 4: Language switching
		$lang_switch = get_option( 'qtranslate_language_switching', 0 );
		if ( ! $lang_switch ) {
			$issues[] = 'Language switching not enabled';
		}

		// Check 5: URL rewrite rules
		$rewrite = get_option( 'qtranslate_url_rewrite_enabled', 0 );
		if ( ! $rewrite ) {
			$issues[] = 'URL rewrite rules not enabled';
		}

		// Check 6: Cookie language preference
		$cookie = get_option( 'qtranslate_cookie_language_preference', 0 );
		if ( ! $cookie ) {
			$issues[] = 'Language cookie preference not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d URL structure issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/qtranslate-x-url-structure',
			);
		}

		return null;
	}
}
