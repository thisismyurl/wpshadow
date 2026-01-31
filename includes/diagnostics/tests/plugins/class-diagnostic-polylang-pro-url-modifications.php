<?php
/**
 * Polylang Pro Url Modifications Diagnostic
 *
 * Polylang Pro Url Modifications misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1146.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Polylang Pro Url Modifications Diagnostic Class
 *
 * @since 1.1146.0000
 */
class Diagnostic_PolylangProUrlModifications extends Diagnostic_Base {

	protected static $slug = 'polylang-pro-url-modifications';
	protected static $title = 'Polylang Pro Url Modifications';
	protected static $description = 'Polylang Pro Url Modifications misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'POLYLANG_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: URL structure
		$url_structure = get_option( 'polylang_url_structure', 'directory' );
		if ( 'directory' === $url_structure ) {
			$issues[] = __( 'Directory structure (subdomain recommended)', 'wpshadow' );
		}

		// Check 2: Language slug conflict
		$languages = pll_languages_list();
		if ( $languages ) {
			foreach ( $languages as $lang ) {
				$page = get_page_by_path( $lang );
				if ( $page ) {
					$issues[] = sprintf( __( 'Language slug /%s/ conflicts with page', 'wpshadow' ), $lang );
				}
			}
		}

		// Check 3: Rewrite rules
		$rules = get_option( 'rewrite_rules' );
		$pll_rules = 0;
		foreach ( $rules as $pattern => $rewrite ) {
			if ( strpos( $pattern, 'language' ) !== false ) {
				$pll_rules++;
			}
		}
		if ( $pll_rules > 50 ) {
			$issues[] = sprintf( __( '%d language rewrite rules (slow routing)', 'wpshadow' ), $pll_rules );
		}

		// Check 4: Redirect handling
		$redirect = get_option( 'polylang_redirect', 'browser' );
		if ( 'browser' === $redirect ) {
			$issues[] = __( 'Browser language detection (caching issues)', 'wpshadow' );
		}

		// Check 5: Canonical URLs
		$canonical = get_option( 'polylang_canonical', 'no' );
		if ( 'no' === $canonical ) {
			$issues[] = __( 'No canonical URLs (duplicate content SEO)', 'wpshadow' );
		}

		// Check 6: Hreflang tags
		$hreflang = get_option( 'polylang_hreflang', 'no' );
		if ( 'no' === $hreflang ) {
			$issues[] = __( 'No hreflang tags (international SEO)', 'wpshadow' );
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
				__( 'Polylang Pro has %d URL modification issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/polylang-pro-url-modifications',
		);
	}
}
