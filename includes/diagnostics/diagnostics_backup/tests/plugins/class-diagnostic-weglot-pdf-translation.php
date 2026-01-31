<?php
/**
 * Weglot Pdf Translation Diagnostic
 *
 * Weglot Pdf Translation misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1160.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Weglot Pdf Translation Diagnostic Class
 *
 * @since 1.1160.0000
 */
class Diagnostic_WeglotPdfTranslation extends Diagnostic_Base {

	protected static $slug = 'weglot-pdf-translation';
	protected static $title = 'Weglot Pdf Translation';
	protected static $description = 'Weglot Pdf Translation misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WEGLOT_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify PDF translation enabled
		$pdf_translation = get_option( 'weglot_pdf_translation_enabled', false );
		if ( ! $pdf_translation ) {
			$issues[] = __( 'PDF translation not enabled', 'wpshadow' );
		}

		// Check 2: Check PDF parsing
		$pdf_parsing = get_option( 'weglot_pdf_parsing_enabled', false );
		if ( ! $pdf_parsing ) {
			$issues[] = __( 'PDF parsing not enabled', 'wpshadow' );
		}

		// Check 3: Verify supported languages
		$languages = get_option( 'weglot_supported_languages', array() );
		if ( empty( $languages ) ) {
			$issues[] = __( 'No translation languages configured', 'wpshadow' );
		}

		// Check 4: Check translation quality
		$quality_level = get_option( 'weglot_translation_quality', '' );
		if ( empty( $quality_level ) ) {
			$issues[] = __( 'PDF translation quality level not set', 'wpshadow' );
		}

		// Check 5: Verify conversion caching
		$pdf_cache = get_transient( 'weglot_pdf_cache' );
		if ( false === $pdf_cache ) {
			$issues[] = __( 'PDF translation caching not active', 'wpshadow' );
		}

		// Check 6: Check API connectivity
		$api_key = get_option( 'weglot_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = __( 'Weglot API key not configured', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Weglot PDF translation issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/weglot-pdf-translation',
			);
		}

		return null;
	}
}

	}
}
