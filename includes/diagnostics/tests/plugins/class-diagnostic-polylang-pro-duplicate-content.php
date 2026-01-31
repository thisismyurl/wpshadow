<?php
/**
 * Polylang Pro Duplicate Content Diagnostic
 *
 * Polylang Pro Duplicate Content misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1147.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Polylang Pro Duplicate Content Diagnostic Class
 *
 * @since 1.1147.0000
 */
class Diagnostic_PolylangProDuplicateContent extends Diagnostic_Base {

	protected static $slug = 'polylang-pro-duplicate-content';
	protected static $title = 'Polylang Pro Duplicate Content';
	protected static $description = 'Polylang Pro Duplicate Content misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'POLYLANG_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Duplicate detection enabled.
		$duplicate_detect = get_option( 'polylang_detect_duplicates', '1' );
		if ( '0' === $duplicate_detect ) {
			$issues[] = 'duplicate detection disabled';
		}
		
		// Check 2: Auto-sync enabled.
		$auto_sync = get_option( 'polylang_auto_sync', '1' );
		if ( '0' === $auto_sync ) {
			$issues[] = 'auto-sync disabled';
		}
		
		// Check 3: Media translation.
		$media_trans = get_option( 'polylang_media_translation', '1' );
		if ( '0' === $media_trans ) {
			$issues[] = 'media translation disabled';
		}
		
		// Check 4: Canonical redirects.
		$canonical = get_option( 'polylang_redirect_canonical', '1' );
		if ( '0' === $canonical ) {
			$issues[] = 'canonical redirects disabled';
		}
		
		// Check 5: Hreflang tags.
		$hreflang = get_option( 'polylang_hreflang', '1' );
		if ( '0' === $hreflang ) {
			$issues[] = 'hreflang tags disabled';
		}
		
		// Check 6: URL modification.
		$url_mod = get_option( 'polylang_url_modification', 'directories' );
		if ( 'none' === $url_mod ) {
			$issues[] = 'URL modification disabled';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 50 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Polylang Pro issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/polylang-pro-duplicate-content',
			);
		}
		
		return null;
	}
}
