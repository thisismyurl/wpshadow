<?php
/**
 * Cache Exclusion Rules Diagnostic
 *
 * Validates cache exclusion configuration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1810
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cache Exclusion Rules Class
 *
 * Checks cache exclusion settings.
 *
 * @since 1.5029.1810
 */
class Diagnostic_Cache_Exclusion_Rules extends Diagnostic_Base {

	protected static $slug        = 'cache-exclusion-rules';
	protected static $title       = 'Cache Exclusion Rules';
	protected static $description = 'Validates exclusion configuration';
	protected static $family      = 'plugins';

	public static function check() {
		$cache_key = 'wpshadow_cache_exclusions';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();

		// Check WP Fastest Cache exclusions.
		if ( class_exists( 'WpFastestCache' ) ) {
			$exclude_pages = get_option( 'WpFastestCacheExclude', array() );
			
			// Check if critical pages are excluded.
			$critical_pages = array( '/cart', '/checkout', '/my-account', '/wp-admin' );
			$excluded_urls = array();
			
			if ( is_array( $exclude_pages ) ) {
				foreach ( $exclude_pages as $rule ) {
					if ( isset( $rule['content'] ) ) {
						$excluded_urls[] = $rule['content'];
					}
				}
			}

			foreach ( $critical_pages as $page ) {
				$is_excluded = false;
				foreach ( $excluded_urls as $url ) {
					if ( strpos( $url, $page ) !== false ) {
						$is_excluded = true;
						break;
					}
				}
				
				if ( ! $is_excluded && $this->page_exists( $page ) ) {
					$issues[] = sprintf( '%s not excluded from cache', $page );
				}
			}

			// Check if logged-in users are excluded.
			$has_logged_in_exclusion = false;
			if ( is_array( $exclude_pages ) ) {
				foreach ( $exclude_pages as $rule ) {
					if ( isset( $rule['type'] ) && 'loggedin' === $rule['type'] ) {
						$has_logged_in_exclusion = true;
						break;
					}
				}
			}

			if ( ! $has_logged_in_exclusion ) {
				$issues[] = 'Logged-in users not excluded from cache';
			}
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d cache exclusion issues. Critical pages may be cached incorrectly.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cache-exclusion-rules',
				'data'         => array(
					'exclusion_issues' => $issues,
					'total_issues' => count( $issues ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}

	private static function page_exists( $slug ) {
		global $wpdb;
		$count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_name LIKE %s AND post_status = 'publish'",
			'%' . $wpdb->esc_like( trim( $slug, '/' ) ) . '%'
		) );
		return $count > 0;
	}
}
