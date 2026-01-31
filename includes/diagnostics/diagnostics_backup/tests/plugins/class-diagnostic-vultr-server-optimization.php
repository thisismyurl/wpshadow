<?php
/**
 * Vultr Server Optimization Diagnostic
 *
 * Vultr Server Optimization needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1020.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vultr Server Optimization Diagnostic Class
 *
 * @since 1.1020.0000
 */
class Diagnostic_VultrServerOptimization extends Diagnostic_Base {

	protected static $slug = 'vultr-server-optimization';
	protected static $title = 'Vultr Server Optimization';
	protected static $description = 'Vultr Server Optimization needs attention';
	protected static $family = 'performance';

	public static function check() {
		// Check for Vultr environment
		$is_vultr = defined( 'VULTR_INSTANCE_ID' ) ||
		            isset( $_SERVER['VULTR_REGION'] ) ||
		            strpos( gethostname(), 'vultr' ) !== false ||
		            file_exists( '/etc/vultr-instance-id' );
		
		if ( ! $is_vultr ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Object storage configured
		$object_storage = get_option( 'vultr_object_storage', 'no' );
		if ( 'no' === $object_storage ) {
			$upload_dir = wp_upload_dir();
			if ( is_dir( $upload_dir['basedir'] ) ) {
				$size = 0;
				$iterator = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator( $upload_dir['basedir'], \RecursiveDirectoryIterator::SKIP_DOTS ),
					\RecursiveIteratorIterator::CATCH_GET_CHILD
				);
				
				foreach ( $iterator as $file ) {
					if ( $file->isFile() ) {
						$size += $file->getSize();
					}
				}
				
				if ( $size > ( 5 * 1024 * 1024 * 1024 ) ) { // 5GB
					$issues[] = sprintf( __( 'Uploads: %s (consider object storage)', 'wpshadow' ), size_format( $size ) );
				}
			}
		}
		
		// Check 2: Block storage for database
		$block_storage = get_option( 'vultr_block_storage', 'no' );
		if ( 'no' === $block_storage ) {
			$issues[] = __( 'Database on instance storage (backup risk)', 'wpshadow' );
		}
		
		// Check 3: Automatic backups
		$auto_backup = get_option( 'vultr_auto_backup', 'no' );
		if ( 'no' === $auto_backup ) {
			$issues[] = __( 'Automatic backups disabled (data loss risk)', 'wpshadow' );
		}
		
		// Check 4: DDoS protection
		$ddos_protection = get_option( 'vultr_ddos_protection', 'basic' );
		if ( 'basic' === $ddos_protection ) {
			$issues[] = __( 'Basic DDoS protection (consider Advanced DDoS)', 'wpshadow' );
		}
		
		// Check 5: Firewall rules
		$firewall_enabled = get_option( 'vultr_firewall', 'no' );
		if ( 'no' === $firewall_enabled ) {
			$issues[] = __( 'Vultr firewall not enabled (security exposure)', 'wpshadow' );
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
				/* translators: %s: list of optimization opportunities */
				__( 'Vultr server has %d optimization opportunities: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/vultr-server-optimization',
		);
	}
}
