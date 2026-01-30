<?php
/**
 * Perfmatters Dns Prefetch Diagnostic
 *
 * Perfmatters Dns Prefetch not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.920.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Perfmatters Dns Prefetch Diagnostic Class
 *
 * @since 1.920.0000
 */
class Diagnostic_PerfmattersDnsPrefetch extends Diagnostic_Base {

	protected static $slug = 'perfmatters-dns-prefetch';
	protected static $title = 'Perfmatters Dns Prefetch';
	protected static $description = 'Perfmatters Dns Prefetch not optimized';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();
		
		// Check 1: DNS prefetch enabled
		$dns = get_option( 'perfmatters_dns_prefetch_enabled', 0 );
		if ( ! $dns ) {
			$issues[] = 'DNS prefetch not enabled';
		}
		
		// Check 2: Prefetch domains configured
		$domains = get_option( 'perfmatters_dns_prefetch_domains_count', 0 );
		if ( $domains <= 0 ) {
			$issues[] = 'No DNS prefetch domains configured';
		}
		
		// Check 3: Connection preload
		$preconnect = get_option( 'perfmatters_preconnect_enabled', 0 );
		if ( ! $preconnect ) {
			$issues[] = 'Preconnect not enabled';
		}
		
		// Check 4: DNS-prefetch vs preconnect optimization
		$opt = get_option( 'perfmatters_prefetch_optimization_enabled', 0 );
		if ( ! $opt ) {
			$issues[] = 'Prefetch optimization not configured';
		}
		
		// Check 5: Unnecessary prefetch cleanup
		$cleanup = get_option( 'perfmatters_unused_prefetch_cleanup_enabled', 0 );
		if ( ! $cleanup ) {
			$issues[] = 'Unused prefetch cleanup not enabled';
		}
		
		// Check 6: Performance impact monitoring
		$monitor = get_option( 'perfmatters_prefetch_performance_monitoring', 0 );
		if ( ! $monitor ) {
			$issues[] = 'Prefetch performance monitoring not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 30;
			$threat_multiplier = 6;
			$max_threat = 60;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d DNS prefetch issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/perfmatters-dns-prefetch',
			);
		}
		
		return null;
	}
}
