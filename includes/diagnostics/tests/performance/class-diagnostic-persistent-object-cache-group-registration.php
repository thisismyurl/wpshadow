<?php
/**
 * Persistent Object Cache Group Registration Diagnostic
 *
 * Verifies cache groups are properly registered for global vs site-specific caching.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Persistent Object Cache Group Registration Class
 *
 * Tests cache group registration.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Persistent_Object_Cache_Group_Registration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'persistent-object-cache-group-registration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Persistent Object Cache Group Registration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies cache groups are properly registered for global vs site-specific caching';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_check = self::check_cache_groups();
		
		if ( $cache_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $cache_check['issues'] ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/persistent-object-cache-group-registration',
				'meta'         => array(
					'object_cache_enabled' => $cache_check['object_cache_enabled'],
					'is_multisite'         => $cache_check['is_multisite'],
					'global_groups'        => $cache_check['global_groups'],
				),
			);
		}

		return null;
	}

	/**
	 * Check cache group registration.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_cache_groups() {
		global $wp_object_cache;

		$check = array(
			'has_issues'           => false,
			'issues'               => array(),
			'object_cache_enabled' => false,
			'is_multisite'         => is_multisite(),
			'global_groups'        => array(),
		);

		// Check if object cache is available.
		$object_cache_file = WP_CONTENT_DIR . '/object-cache.php';
		$check['object_cache_enabled'] = file_exists( $object_cache_file );

		if ( ! $check['object_cache_enabled'] ) {
			return $check; // No object cache, nothing to check.
		}

		// Get global cache groups.
		if ( isset( $wp_object_cache->global_groups ) && is_array( $wp_object_cache->global_groups ) ) {
			$check['global_groups'] = $wp_object_cache->global_groups;
		}

		// Required global groups for multisite.
		$required_global_groups = array( 'users', 'userlogins', 'usermeta', 'user_meta', 'useremail', 'userslugs', 'site-transient', 'site-options', 'blog-lookup', 'blog-details', 'site-details', 'networks', 'rss', 'global-posts', 'blog-id-cache', 'global-cache-test' );

		if ( $check['is_multisite'] ) {
			// Check if required groups are global.
			$missing_groups = array_diff( $required_global_groups, $check['global_groups'] );

			if ( ! empty( $missing_groups ) ) {
				$check['has_issues'] = true;
				$check['issues'][] = sprintf(
					/* translators: %s: comma-separated list of missing groups */
					__( 'Multisite cache groups not properly registered as global: %s (cache collision risk)', 'wpshadow' ),
					implode( ', ', array_slice( $missing_groups, 0, 5 ) )
				);
			}
		}

		// Check if non-persistent groups are registered.
		if ( isset( $wp_object_cache->non_persistent_groups ) && is_array( $wp_object_cache->non_persistent_groups ) ) {
			$expected_non_persistent = array( 'comment', 'counts', 'plugins' );
			$registered_non_persistent = $wp_object_cache->non_persistent_groups;

			// Check if expected non-persistent groups are registered.
			$missing_non_persistent = array_diff( $expected_non_persistent, $registered_non_persistent );

			if ( count( $missing_non_persistent ) === count( $expected_non_persistent ) ) {
				$check['has_issues'] = true;
				$check['issues'][] = __( 'Non-persistent cache groups not registered (comment counts may be stale)', 'wpshadow' );
			}
		}

		return $check;
	}
}
