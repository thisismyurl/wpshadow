<?php
/**
 * TranslatePress SEO Pack Diagnostic
 *
 * TranslatePress SEO not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.314.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TranslatePress SEO Pack Diagnostic Class
 *
 * @since 1.314.0000
 */
class Diagnostic_TranslatepressSeoPack extends Diagnostic_Base {

	protected static $slug = 'translatepress-seo-pack';
	protected static $title = 'TranslatePress SEO Pack';
	protected static $description = 'TranslatePress SEO not configured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'TRP_PLUGIN_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify SEO pack enabled
		$seo_pack_enabled = get_option( 'trp_seo_pack_enabled', false );
		if ( ! $seo_pack_enabled ) {
			$issues[] = __( 'TranslatePress SEO pack not enabled', 'wpshadow' );
		}

		// Check 2: Check hreflang tags
		$hreflang_enabled = get_option( 'trp_hreflang_tags', false );
		if ( ! $hreflang_enabled ) {
			$issues[] = __( 'Hreflang tags not configured', 'wpshadow' );
		}

		// Check 3: Verify SEO-friendly URLs
		$seo_urls = get_option( 'trp_seo_friendly_urls', false );
		if ( ! $seo_urls ) {
			$issues[] = __( 'SEO-friendly URLs not enabled', 'wpshadow' );
		}

		// Check 4: Check language URL structure
		$url_structure = get_option( 'trp_translation_url_structure', '' );
		if ( empty( $url_structure ) ) {
			$issues[] = __( 'Translation URL structure not configured', 'wpshadow' );
		}

		// Check 5: Verify meta tags translation
		$meta_translation = get_option( 'trp_meta_tags_translation', false );
		if ( ! $meta_translation ) {
			$issues[] = __( 'Meta tags translation not enabled', 'wpshadow' );
		}

		// Check 6: Check schema markup translation
		$schema_translation = get_option( 'trp_schema_markup_translation', false );
		if ( ! $schema_translation ) {
			$issues[] = __( 'Schema markup translation not enabled', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 80, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'TranslatePress SEO pack issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/translatepress-seo-pack',
			);
		}

		return null;
	}
}
