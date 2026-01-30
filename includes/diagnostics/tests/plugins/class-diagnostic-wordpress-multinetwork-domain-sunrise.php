<?php
/**
 * Wordpress Multinetwork Domain Sunrise Diagnostic
 *
 * Wordpress Multinetwork Domain Sunrise misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.958.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Multinetwork Domain Sunrise Diagnostic Class
 *
 * @since 1.958.0000
 */
class Diagnostic_WordpressMultinetworkDomainSunrise extends Diagnostic_Base {

	protected static $slug = 'wordpress-multinetwork-domain-sunrise';
	protected static $title = 'Wordpress Multinetwork Domain Sunrise';
	protected static $description = 'Wordpress Multinetwork Domain Sunrise misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}
		
		$issues = array();
		
		// Check if SUNRISE is defined
		if ( ! defined( 'SUNRISE' ) || ! SUNRISE ) {
			$issues[] = 'SUNRISE constant not enabled in wp-config.php';
		}
		
		// Check if sunrise.php exists
		$sunrise_file = WP_CONTENT_DIR . '/sunrise.php';
		if ( ! file_exists( $sunrise_file ) ) {
			$issues[] = 'sunrise.php file missing from wp-content directory';
		}
		
		// Check if DOMAIN_CURRENT_SITE is set for multinetwork
		if ( defined( 'SUNRISE' ) && SUNRISE ) {
			if ( ! defined( 'DOMAIN_CURRENT_SITE' ) || empty( DOMAIN_CURRENT_SITE ) ) {
				$issues[] = 'DOMAIN_CURRENT_SITE constant not properly configured';
			}
		}
		
		// Check if multinetwork plugin is active
		if ( ! class_exists( 'WP_MS_Networks_Plugin' ) && ! function_exists( 'switch_to_network' ) ) {
			$issues[] = 'multinetwork plugin not active but sunrise configured';
		}
		
		// Check sunrise.php permissions
		if ( file_exists( $sunrise_file ) ) {
			$perms = fileperms( $sunrise_file );
			if ( ( $perms & 0222 ) > 0 ) {
				$issues[] = 'sunrise.php is writable (should be read-only)';
			}
		}
		
		// Check for network table issues
		global $wpdb;
		$network_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}site" );
		if ( defined( 'SUNRISE' ) && SUNRISE && $network_count < 1 ) {
			$issues[] = 'sunrise enabled but no networks found in database';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 50 + ( count( $issues ) * 7 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'WordPress Multinetwork domain mapping issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wordpress-multinetwork-domain-sunrise',
			);
		}
		
		return null;
	}
}
