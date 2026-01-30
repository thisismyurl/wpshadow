<?php
/**
 * PeepSo Performance Diagnostic
 *
 * PeepSo slowing site significantly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.518.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PeepSo Performance Diagnostic Class
 *
 * @since 1.518.0000
 */
class Diagnostic_PeepsoPerformance extends Diagnostic_Base {

	protected static $slug = 'peepso-performance';
	protected static $title = 'PeepSo Performance';
	protected static $description = 'PeepSo slowing site significantly';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'PeepSo' ) ) {
			return null;
		}
		
		// Check if PeepSo is active
		if ( ! class_exists( 'PeepSo' ) && ! defined( 'PEEPSO_VERSION' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check object caching
		$object_cache = wp_using_ext_object_cache();
		if ( ! $object_cache ) {
			$issues[] = 'object_cache_not_configured';
			$threat_level += 30;
		}

		// Check asset minification
		$minify_js = get_option( 'peepso_minify_js', 1 );
		$minify_css = get_option( 'peepso_minify_css', 1 );
		if ( ! $minify_js || ! $minify_css ) {
			$issues[] = 'asset_minification_disabled';
			$threat_level += 20;
		}

		// Check user count
		$user_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );
		if ( $user_count > 1000 ) {
			// Check user query caching
			$user_cache = get_option( 'peepso_cache_users', 1 );
			if ( ! $user_cache ) {
				$issues[] = 'user_cache_disabled';
				$threat_level += 25;
			}
		}

		// Check AJAX rate limiting
		$rate_limit = get_option( 'peepso_ajax_rate_limit', 0 );
		if ( ! $rate_limit ) {
			$issues[] = 'ajax_rate_limiting_disabled';
			$threat_level += 20;
		}

		// Check lazy image loading
		$lazy_images = get_option( 'peepso_lazy_load_images', 1 );
		if ( ! $lazy_images ) {
			$issues[] = 'image_lazy_loading_disabled';
			$threat_level += 15;
		}

		// Check notification cleanup
		$old_notifications = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}peepso_notifications 
			 WHERE timestamp < DATE_SUB(NOW(), INTERVAL 90 DAY)"
		);
		if ( $old_notifications > 10000 ) {
			$issues[] = 'old_notifications_not_cleaned';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of performance issues */
				__( 'PeepSo has significant performance issues: %s. This slows down the entire site and affects all users.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/peepso-performance',
			);
		}
		
		return null;
	}
}
