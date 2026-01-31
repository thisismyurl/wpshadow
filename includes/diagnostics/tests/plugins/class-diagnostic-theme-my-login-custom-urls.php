<?php
/**
 * Theme My Login Custom Urls Diagnostic
 *
 * Theme My Login Custom Urls issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1235.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme My Login Custom Urls Diagnostic Class
 *
 * @since 1.1235.0000
 */
class Diagnostic_ThemeMyLoginCustomUrls extends Diagnostic_Base {

	protected static $slug = 'theme-my-login-custom-urls';
	protected static $title = 'Theme My Login Custom Urls';
	protected static $description = 'Theme My Login Custom Urls issue found';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();
		
		// Check 1: Custom login URL configured
		$login_url = get_option( 'tml_login_slug', '' );
		if ( empty( $login_url ) ) {
			$issues[] = 'Custom login URL not configured';
		}
		
		// Check 2: Custom registration URL
		$register_url = get_option( 'tml_register_slug', '' );
		if ( empty( $register_url ) ) {
			$issues[] = 'Custom registration URL not configured';
		}
		
		// Check 3: Custom lost password URL
		$lostpw_url = get_option( 'tml_lostpassword_slug', '' );
		if ( empty( $lostpw_url ) ) {
			$issues[] = 'Custom lost password URL not configured';
		}
		
		// Check 4: Custom logout URL
		$logout_url = get_option( 'tml_logout_slug', '' );
		if ( empty( $logout_url ) ) {
			$issues[] = 'Custom logout URL not configured';
		}
		
		// Check 5: URL conflict detection
		$conflict_check = get_option( 'tml_conflict_detection', false );
		if ( ! $conflict_check ) {
			$issues[] = 'URL conflict detection disabled';
		}
		
		// Check 6: Rewrite rules flushed
		$rewrite_flushed = get_option( 'tml_rewrite_flushed', false );
		if ( ! $rewrite_flushed ) {
			$issues[] = 'Rewrite rules need flushing';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 35 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Theme My Login custom URL issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/theme-my-login-custom-urls',
			);
		}
		
		return null;
	}
}
