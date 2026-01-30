<?php
/**
 * TranslatePress Media Translation Diagnostic
 *
 * TranslatePress media not translated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.315.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TranslatePress Media Translation Diagnostic Class
 *
 * @since 1.315.0000
 */
class Diagnostic_TranslatepressMediaTranslation extends Diagnostic_Base {

	protected static $slug = 'translatepress-media-translation';
	protected static $title = 'TranslatePress Media Translation';
	protected static $description = 'TranslatePress media not translated';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'TRP_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Media translation enabled
		$media_enabled = get_option( 'trp_translate_media', 'no' );
		if ( 'no' === $media_enabled ) {
			$issues[] = __( 'Media translation disabled (untranslated images)', 'wpshadow' );
		}
		
		// Check 2: Alt text translation
		$alt_text = get_option( 'trp_translate_alt_text', 'no' );
		if ( 'no' === $alt_text ) {
			$issues[] = __( 'Alt text not translated (SEO/accessibility)', 'wpshadow' );
		}
		
		// Check 3: Caption translation
		$captions = get_option( 'trp_translate_captions', 'no' );
		if ( 'no' === $captions ) {
			$issues[] = __( 'Captions not translated (incomplete localization)', 'wpshadow' );
		}
		
		// Check 4: Filename translation
		$filenames = get_option( 'trp_translate_filenames', 'no' );
		if ( 'no' === $filenames ) {
			$issues[] = __( 'Filenames not translated (SEO impact)', 'wpshadow' );
		}
		
		// Check 5: Duplicate media handling
		$duplicate_media = get_option( 'trp_duplicate_media', 'no' );
		if ( 'yes' === $duplicate_media ) {
			$issues[] = __( 'Duplicating media files (disk space)', 'wpshadow' );
		}
		
		// Check 6: Media library organization
		$organize = get_option( 'trp_organize_media', 'no' );
		if ( 'no' === $organize ) {
			$issues[] = __( 'Translated media not organized (confusion)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 35;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 47;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 41;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'TranslatePress media has %d translation issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/translatepress-media-translation',
		);
	}
}
