<?php
/**
 * Digitalocean Droplet Performance Diagnostic
 *
 * Digitalocean Droplet Performance needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1017.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Digitalocean Droplet Performance Diagnostic Class
 *
 * @since 1.1017.0000
 */
class Diagnostic_DigitaloceanDropletPerformance extends Diagnostic_Base {

	protected static $slug = 'digitalocean-droplet-performance';
	protected static $title = 'Digitalocean Droplet Performance';
	protected static $description = 'Digitalocean Droplet Performance needs attention';
	protected static $family = 'performance';

	public static function check() {
		// Check for DigitalOcean hosting indicators
		$is_digitalocean = isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) &&
		                   ( strpos( gethostname(), 'digitalocean' ) !== false ||
		                     defined( 'DIGITALOCEAN_DROPLET' ) );
		
		if ( ! $is_digitalocean ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: PHP memory limit vs droplet size
		$memory_limit = ini_get( 'memory_limit' );
		$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );
		
		if ( $memory_bytes > 536870912 ) { // 512MB
			$issues[] = sprintf( __( 'PHP memory limit (%s) high for basic droplet', 'wpshadow' ), $memory_limit );
		}
		
		// Check 2: Object caching
		$has_object_cache = wp_using_ext_object_cache();
		if ( ! $has_object_cache ) {
			$issues[] = __( 'No object caching (Redis/Memcached recommended on DigitalOcean)', 'wpshadow' );
		}
		
		// Check 3: Monitoring enabled
		$monitoring = get_option( 'digitalocean_monitoring_enabled', false );
		if ( ! $monitoring ) {
			$issues[] = __( 'DigitalOcean monitoring agent not detected', 'wpshadow' );
		}
		
		// Check 4: Backup configuration
		$backups_enabled = get_option( 'digitalocean_backups_enabled', false );
		if ( ! $backups_enabled ) {
			$issues[] = __( 'DigitalOcean automated backups not enabled', 'wpshadow' );
		}
		
		// Check 5: Spaces CDN integration
		$spaces_cdn = get_option( 'digitalocean_spaces_cdn', false );
		if ( ! $spaces_cdn ) {
			$issues[] = __( 'DigitalOcean Spaces CDN not configured (media delivery)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 68;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 62;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of performance issues */
				__( 'DigitalOcean Droplet has %d optimization opportunities: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/digitalocean-droplet-performance',
		);
	}
}
