<?php
/**
 * Wpml Slugs Translation Seo Diagnostic
 *
 * Wpml Slugs Translation Seo misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1141.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wpml Slugs Translation Seo Diagnostic Class
 *
 * @since 1.1141.0000
 */
class Diagnostic_WpmlSlugsTranslationSeo extends Diagnostic_Base {

	protected static $slug = 'wpml-slugs-translation-seo';
	protected static $title = 'Wpml Slugs Translation Seo';
	protected static $description = 'Wpml Slugs Translation Seo misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return null;
		}

		$issues = array();
		$wpml_settings = get_option( 'icl_sitepress_settings', array() );

		// Check 1: Slug translation enabled
		$slug_trans = isset( $wpml_settings['enable_slug_translation'] ) ? (bool) $wpml_settings['enable_slug_translation'] : false;
		if ( ! $slug_trans ) {
			$issues[] = 'Slug translation not enabled';
		}

		// Check 2: SEO optimization enabled
		$seo_opt = isset( $wpml_settings['seo_friendly_urls'] ) ? (bool) $wpml_settings['seo_friendly_urls'] : false;
		if ( ! $seo_opt ) {
			$issues[] = 'SEO-friendly URLs not enabled';
		}

		// Check 3: Canonical tags
		$canonical = get_option( 'wpml_canonical_tags_enabled', 0 );
		if ( ! $canonical ) {
			$issues[] = 'Canonical tags not enabled';
		}

		// Check 4: Duplicate content handling
		$duplicate_handling = get_option( 'wpml_duplicate_content_handling', '' );
		if ( empty( $duplicate_handling ) ) {
			$issues[] = 'Duplicate content handling not configured';
		}

		// Check 5: Hreflang tags
		$hreflang = get_option( 'wpml_hreflang_tags_enabled', 0 );
		if ( ! $hreflang ) {
			$issues[] = 'Hreflang tags not enabled';
		}

		// Check 6: Sitemap generation
		$sitemap = get_option( 'wpml_sitemap_generation', 0 );
		if ( ! $sitemap ) {
			$issues[] = 'Sitemap generation for translations not enabled';
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
					'Found %d WPML SEO issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wpml-slugs-translation-seo',
			);
		}

		return null;
	}
}
