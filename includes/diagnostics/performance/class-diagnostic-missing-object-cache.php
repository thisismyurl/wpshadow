<?php
/**
 * Diagnostic: Missing Object Cache
 *
 * Detects if persistent object caching (Redis/Memcached) is not configured.
 *
 * Philosophy: Show Value (#9) - Prove that caching = speed
 * KB Link: https://wpshadow.com/kb/missing-object-cache
 * Training: https://wpshadow.com/training/missing-object-cache
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing Object Cache diagnostic
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Missing_Object_Cache extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Diagnostic result or null if no issue
	 */
	public static function check(): ?array {
		// Check if persistent object cache is enabled
		if ( wp_using_ext_object_cache() ) {
			return null; // Cache is configured
		}

		// Check if site is large enough to benefit
		global $wpdb;
		
		$post_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish'" );
		$user_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );
		$option_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options}" );

		// Only recommend if site is medium-large
		if ( $post_count < 100 && $user_count < 50 ) {
			return null; // Small site, not critical
		}

		$severity = $post_count > 1000 ? 'high' : 'medium';

		$description = sprintf(
			__( 'Your site has %s posts, %s users, and %s options but no persistent object cache. Adding Redis or Memcached can reduce database queries by 50-80%% and speed up your site significantly.', 'wpshadow' ),
			number_format( $post_count ),
			number_format( $user_count ),
			number_format( $option_count )
		);

		// Check if Redis/Memcached extensions are available
		$available_options = [];
		if ( class_exists( 'Redis' ) ) {
			$available_options[] = 'Redis (PHP extension detected)';
		}
		if ( class_exists( 'Memcached' ) ) {
			$available_options[] = 'Memcached (PHP extension detected)';
		}

		if ( ! empty( $available_options ) ) {
			$description .= ' ' . __( 'Available: ', 'wpshadow' ) . implode( ', ', $available_options );
		}

		return [
			'id'                => 'missing-object-cache',
			'title'             => __( 'No Object Cache Configured', 'wpshadow' ),
			'description'       => $description,
			'severity'          => $severity,
			'category'          => 'performance',
			'impact'            => 'high',
			'effort'            => 'medium',
			'kb_link'           => 'https://wpshadow.com/kb/missing-object-cache',
			'training_link'     => 'https://wpshadow.com/training/missing-object-cache',
			'affected_resource' => sprintf( '%s posts, %s users', number_format( $post_count ), number_format( $user_count ) ),
			'metadata'          => [
				'post_count'       => $post_count,
				'user_count'       => $user_count,
				'option_count'     => $option_count,
				'redis_available'  => class_exists( 'Redis' ),
				'memcached_available' => class_exists( 'Memcached' ),
				'potential_speedup' => '50-80% fewer DB queries',
			],
		];
	}

}