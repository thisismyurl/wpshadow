<?php
/**
 * Multi Language Browser Detection Diagnostic
 *
 * Multi Language Browser Detection misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1185.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multi Language Browser Detection Diagnostic Class
 *
 * @since 1.1185.0000
 */
class Diagnostic_MultiLanguageBrowserDetection extends Diagnostic_Base {

	protected static $slug = 'multi-language-browser-detection';
	protected static $title = 'Multi Language Browser Detection';
	protected static $description = 'Multi Language Browser Detection misconfigured';
	protected static $family = 'functionality';

	public static function check() {

		$issues = array();

		// Check 1: Verify browser language detection is enabled
		$browser_detection = get_option( 'multilang_browser_detection', false );
		if ( ! $browser_detection ) {
			$issues[] = __( 'Browser language detection not enabled', 'wpshadow' );
		}

		// Check 2: Check language fallback configuration
		$language_fallback = get_option( 'multilang_fallback_language', '' );
		if ( empty( $language_fallback ) ) {
			$issues[] = __( 'Language fallback not configured', 'wpshadow' );
		}

		// Check 3: Verify auto-redirect configuration
		$auto_redirect = get_option( 'multilang_auto_redirect', false );
		if ( $auto_redirect ) {
			$issues[] = __( 'Auto-redirect enabled (may affect user experience)', 'wpshadow' );
		}

		// Check 4: Check language preference caching
		$preference_cache = get_transient( 'multilang_user_preference_cache' );
		if ( false === $preference_cache ) {
			$issues[] = __( 'Language preference caching not active', 'wpshadow' );
		}

		// Check 5: Verify detection accuracy settings
		$detection_accuracy = get_option( 'multilang_detection_accuracy', '' );
		if ( 'high' !== $detection_accuracy ) {
			$issues[] = __( 'Detection accuracy not set to high', 'wpshadow' );
		}

		// Check 6: Check cookie/session handling
		$session_handling = get_option( 'multilang_session_handling', false );
		if ( ! $session_handling ) {
			$issues[] = __( 'Session-based language preference not enabled', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 80, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Multi-language browser detection issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/multi-language-browser-detection',
			);
		}

		return null;
	}
}
