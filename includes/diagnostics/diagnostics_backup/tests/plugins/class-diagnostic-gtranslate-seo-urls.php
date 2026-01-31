<?php
/**
 * Gtranslate Seo Urls Diagnostic
 *
 * Gtranslate Seo Urls misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1163.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gtranslate Seo Urls Diagnostic Class
 *
 * @since 1.1163.0000
 */
class Diagnostic_GtranslateSeoUrls extends Diagnostic_Base {

	protected static $slug = 'gtranslate-seo-urls';
	protected static $title = 'Gtranslate Seo Urls';
	protected static $description = 'Gtranslate Seo Urls misconfigured';
	protected static $family = 'functionality';

	public static function check() {
			// Check if GTranslate is active
		if ( ! class_exists( 'GTranslate' ) && ! function_exists( 'gtranslate_load_config_json' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Check URL structure
		$settings = get_option( 'GTranslate', array() );
		$url_structure = isset( $settings['pro_version'] ) ? $settings['pro_version'] : 'free';
		
		if ( $url_structure === 'free' ) {
			$issues[] = 'using_non_seo_url_structure';
			$threat_level += 30;
		}

		// Check hreflang tags
		$add_hreflang = isset( $settings['add_hreflang_tags'] ) ? $settings['add_hreflang_tags'] : 'no';
		if ( $add_hreflang === 'no' ) {
			$issues[] = 'hreflang_tags_disabled';
			$threat_level += 25;
		}

		// Check canonical URLs
		$canonical = isset( $settings['add_canonical_tags'] ) ? $settings['add_canonical_tags'] : 'no';
		if ( $canonical === 'no' ) {
			$issues[] = 'canonical_tags_disabled';
			$threat_level += 25;
		}

		// Check sitemap
		$sitemap_enabled = isset( $settings['enable_language_sitemap'] ) ? $settings['enable_language_sitemap'] : 'no';
		if ( $sitemap_enabled === 'no' ) {
			$issues[] = 'language_sitemap_disabled';
			$threat_level += 20;
		}

		// Check noindex meta
		$auto_translate = isset( $settings['auto_translate'] ) ? $settings['auto_translate'] : 'no';
		if ( $auto_translate === 'yes' ) {
			$noindex_auto = isset( $settings['add_noindex_to_auto_translated'] ) ? $settings['add_noindex_to_auto_translated'] : 'no';
			if ( $noindex_auto === 'no' ) {
				$issues[] = 'auto_translated_pages_indexed';
				$threat_level += 15;
			}
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of SEO issues */
				__( 'GTranslate SEO URLs have configuration problems: %s. This harms multilingual SEO and search engine indexation.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/gtranslate-seo-urls',
			);
		}
		
		return null;
	}
}
