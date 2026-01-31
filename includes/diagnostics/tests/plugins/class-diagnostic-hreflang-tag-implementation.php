<?php
/**
 * Hreflang Tag Implementation Diagnostic
 *
 * Hreflang Tag Implementation misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1186.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hreflang Tag Implementation Diagnostic Class
 *
 * @since 1.1186.0000
 */
class Diagnostic_HreflangTagImplementation extends Diagnostic_Base {

	protected static $slug = 'hreflang-tag-implementation';
	protected static $title = 'Hreflang Tag Implementation';
	protected static $description = 'Hreflang Tag Implementation misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: Verify hreflang output enabled
		$hreflang_enabled = get_option( 'hreflang_enabled', 0 );
		if ( ! $hreflang_enabled ) {
			$issues[] = 'Hreflang output not enabled';
		}

		// Check 2: Check for x-default tag
		$x_default = get_option( 'hreflang_x_default', 0 );
		if ( ! $x_default ) {
			$issues[] = 'x-default hreflang tag not configured';
		}

		// Check 3: Verify canonical/hreflang conflicts
		$canonical_override = get_option( 'hreflang_canonical_override', 0 );
		if ( $canonical_override ) {
			$issues[] = 'Canonical URL overriding hreflang tags';
		}

		// Check 4: Check for auto-detection
		$auto_detect = get_option( 'hreflang_auto_detect', 0 );
		if ( $auto_detect ) {
			$issues[] = 'Automatic language detection enabled (can cause mismatches)';
		}

		// Check 5: Verify hreflang in sitemap
		$sitemap_hreflang = get_option( 'hreflang_sitemap', 0 );
		if ( ! $sitemap_hreflang ) {
			$issues[] = 'Hreflang not included in sitemap';
		}

		// Check 6: Check for missing language mappings
		$language_map = get_option( 'hreflang_language_map', array() );
		if ( empty( $language_map ) ) {
			$issues[] = 'Language mapping not configured';
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
					'Found %d hreflang implementation issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/hreflang-tag-implementation',
			);
		}

		return null;
	}
}
