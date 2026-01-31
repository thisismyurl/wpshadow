<?php
/**
 * All In One Seo Redirects Diagnostic
 *
 * All In One Seo Redirects configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.703.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All In One Seo Redirects Diagnostic Class
 *
 * @since 1.703.0000
 */
class Diagnostic_AllInOneSeoRedirects extends Diagnostic_Base {

	protected static $slug = 'all-in-one-seo-redirects';
	protected static $title = 'All In One Seo Redirects';
	protected static $description = 'All In One Seo Redirects configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		// Check if AIOSEO is installed
		if ( ! function_exists( 'aioseo' ) ) {
			return null;
		}

		global $wpdb;
		$issues = array();
		$threat_level = 0;

		// Check redirects table
		$table_name = $wpdb->prefix . 'aioseo_redirects';
		$redirect_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
		if ( $redirect_count > 1000 ) {
			$issues[] = 'excessive_redirects';
			$threat_level += 15;
		}

		// Check for redirect chains
		$chains = $wpdb->get_results(
			"SELECT source_url, target_url FROM {$table_name} WHERE enabled = 1",
			ARRAY_A
		);
		$chain_count = 0;
		foreach ( $chains as $redirect ) {
			foreach ( $chains as $check ) {
				if ( $redirect['target_url'] === $check['source_url'] ) {
					$chain_count++;
				}
			}
		}
		if ( $chain_count > 5 ) {
			$issues[] = 'redirect_chains_detected';
			$threat_level += 20;
		}

		// Check 404 monitoring
		$log_table = $wpdb->prefix . 'aioseo_404_logs';
		$log_size = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS size_mb
				 FROM information_schema.TABLES
				 WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
				DB_NAME,
				$log_table
			)
		);
		if ( $log_size > 50 ) {
			$issues[] = '404_log_too_large';
			$threat_level += 10;
		}

		// Check regex redirects
		$regex_redirects = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE type = %s",
				'regex'
			)
		);
		if ( $regex_redirects > 50 ) {
			$issues[] = 'excessive_regex_redirects';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of redirect issues */
				__( 'All in One SEO redirects has problems: %s. This can cause slow page loads, redirect loops, and bloated databases.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-seo-redirects',
			);
		}
		
		return null;
	}
}
