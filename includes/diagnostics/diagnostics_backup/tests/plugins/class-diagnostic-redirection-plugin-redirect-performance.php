<?php
/**
 * Redirection Plugin Redirect Performance Diagnostic
 *
 * Redirection Plugin Redirect Performance issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1419.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redirection Plugin Redirect Performance Diagnostic Class
 *
 * @since 1.1419.0000
 */
class Diagnostic_RedirectionPluginRedirectPerformance extends Diagnostic_Base {

	protected static $slug = 'redirection-plugin-redirect-performance';
	protected static $title = 'Redirection Plugin Redirect Performance';
	protected static $description = 'Redirection Plugin Redirect Performance issue found';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'REDIRECTION_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Redirect caching enabled
		$redirect_cache = get_option( 'redirection_cache_enabled', false );
		if ( ! $redirect_cache ) {
			$issues[] = 'Redirect caching disabled';
		}

		// Check 2: 301 redirects preferred over 302
		$default_type = get_option( 'redirection_default_type', 302 );
		if ( 302 === $default_type ) {
			$issues[] = '302 redirects used (301 preferred for SEO)';
		}

		// Check 3: Regex redirects optimized
		$regex_optimization = get_option( 'redirection_regex_optimization', false );
		if ( ! $regex_optimization ) {
			$issues[] = 'Regex redirects not optimized';
		}

		// Check 4: Redirect logging limited
		$log_limit = get_option( 'redirection_log_limit', 0 );
		if ( $log_limit <= 0 || $log_limit > 1000 ) {
			$issues[] = 'Redirect logging limit not configured';
		}

		// Check 5: Database cleanup enabled
		$db_cleanup = get_option( 'redirection_database_cleanup', false );
		if ( ! $db_cleanup ) {
			$issues[] = 'Database cleanup disabled';
		}

		// Check 6: Performance monitoring enabled
		$perf_monitoring = get_option( 'redirection_performance_monitoring', false );
		if ( ! $perf_monitoring ) {
			$issues[] = 'Performance monitoring disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Redirection plugin performance issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/redirection-plugin-redirect-performance',
			);
		}
		// Check transient support
		if ( ! function_exists( 'set_transient' ) ) {
			$issues[] = __( 'Transient functions unavailable', 'wpshadow' );
		}
		return null;
	}
}
