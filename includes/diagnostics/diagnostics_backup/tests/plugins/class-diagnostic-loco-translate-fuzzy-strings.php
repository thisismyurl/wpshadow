<?php
/**
 * Loco Translate Fuzzy Strings Diagnostic
 *
 * Loco Translate Fuzzy Strings misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1170.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loco Translate Fuzzy Strings Diagnostic Class
 *
 * @since 1.1170.0000
 */
class Diagnostic_LocoTranslateFuzzyStrings extends Diagnostic_Base {

	protected static $slug = 'loco-translate-fuzzy-strings';
	protected static $title = 'Loco Translate Fuzzy Strings';
	protected static $description = 'Loco Translate Fuzzy Strings misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'LOCO_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Fuzzy string handling.
		$fuzzy_handling = get_option( 'loco_handle_fuzzy', '1' );
		if ( '0' === $fuzzy_handling ) {
			$issues[] = 'fuzzy strings not handled';
		}

		// Check 2: Auto-sync.
		$auto_sync = get_option( 'loco_auto_sync', '1' );
		if ( '0' === $auto_sync ) {
			$issues[] = 'automatic sync disabled';
		}

		// Check 3: Backup translations.
		$backup = get_option( 'loco_backup_translations', '1' );
		if ( '0' === $backup ) {
			$issues[] = 'translation backups disabled';
		}

		// Check 4: Template caching.
		$template_cache = get_option( 'loco_cache_templates', '1' );
		if ( '0' === $template_cache ) {
			$issues[] = 'template caching disabled';
		}

		// Check 5: Compile MO files.
		$compile_mo = get_option( 'loco_compile_mo', '1' );
		if ( '0' === $compile_mo ) {
			$issues[] = 'MO compilation disabled';
		}

		// Check 6: String extraction.
		$extraction = get_option( 'loco_extract_strings', '1' );
		if ( '0' === $extraction ) {
			$issues[] = 'string extraction disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 50 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Loco Translate issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/loco-translate-fuzzy-strings',
			);
		}

		return null;
	}
}
