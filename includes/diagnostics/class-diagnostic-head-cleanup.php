<?php
/**
 * Head Cleanup Diagnostic
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

/**
 * Check if common head cruft is still enabled.
 */
class Diagnostic_Head_Cleanup {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$issues = array();
		
		if ( self::is_emoji_enabled() ) {
			$issues[] = 'emoji scripts';
		}
		if ( self::is_oembed_enabled() ) {
			$issues[] = 'oEmbed discovery';
		}
		if ( self::is_rsd_enabled() ) {
			$issues[] = 'RSD link';
		}
		if ( self::is_shortlink_enabled() ) {
			$issues[] = 'wp-shortlink';
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$summary = ucfirst( implode( ', ', $issues ) );
		
		return array(
			'id'           => 'head-cleanup-needed',
			'title'        => 'Clean Up Page Head',
			'description'  => $summary . ' still load on your pages. Removing them reduces requests and exposure.',
			'color'        => '#ff9800',
			'bg_color'     => '#fff3e0',
			'kb_link'      => 'https://wpshadow.com/kb/head-cleanup/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=head-cleanup',
			'auto_fixable' => true,
			'threat_level' => 35,
		);
	}
	
	private static function is_emoji_enabled() {
		return has_action( 'wp_head', 'print_emoji_detection_script' ) || has_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	}
	
	private static function is_oembed_enabled() {
		return has_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	}
	
	private static function is_rsd_enabled() {
		return has_action( 'wp_head', 'rsd_link' );
	}
	
	private static function is_shortlink_enabled() {
		return has_action( 'wp_head', 'wp_shortlink_wp_head' );
	}
}
