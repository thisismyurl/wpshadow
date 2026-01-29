<?php
/**
 * Transient Caching Strategy Validation Diagnostic
 *
 * Validates transients use object cache (not database) when available.
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
 * Transient Caching Strategy Validation Class
 *
 * Tests transient caching strategy.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Transient_Caching_Strategy_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'transient-caching-strategy-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Transient Caching Strategy Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates transients use object cache (not database) when available';

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
		$transient_check = self::check_transient_strategy();
		
		if ( $transient_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $transient_check['issues'] ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/transient-caching-strategy-validation',
				'meta'         => array(
					'object_cache_enabled' => $transient_check['object_cache_enabled'],
					'db_transient_count'   => $transient_check['db_transient_count'],
				),
			);
		}

		return null;
	}

	/**
	 * Check transient caching strategy.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_transient_strategy() {
		global $wpdb;

		$check = array(
			'has_issues'           => false,
			'issues'               => array(),
			'object_cache_enabled' => false,
			'db_transient_count'   => 0,
		);

		// Check if object cache is available.
		$object_cache_file = WP_CONTENT_DIR . '/object-cache.php';
		$check['object_cache_enabled'] = file_exists( $object_cache_file );

		// Count transients in database.
		$check['db_transient_count'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				$wpdb->esc_like( '_transient_' ) . '%',
				$wpdb->esc_like( '_site_transient_' ) . '%'
			)
		);

		// If object cache is enabled but many transients in DB, flag it.
		if ( $check['object_cache_enabled'] && $check['db_transient_count'] > 100 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of transients */
				__( 'Object cache enabled but %d transients still in database (cache not being used optimally)', 'wpshadow' ),
				$check['db_transient_count']
			);
		}

		// Test if transients actually use cache.
		if ( $check['object_cache_enabled'] ) {
			$test_key = 'wpshadow_transient_test_' . time();
			$test_value = 'test_' . wp_rand( 1000, 9999 );

			// Set transient.
			set_transient( $test_key, $test_value, 300 );

			// Check if it's in database.
			$db_value = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
					'_transient_' . $test_key
				)
			);

			if ( null !== $db_value ) {
				$check['has_issues'] = true;
				$check['issues'][] = __( 'Transients being stored in database despite object cache (cache bypass detected)', 'wpshadow' );
			}

			// Clean up test.
			delete_transient( $test_key );
		}

		return $check;
	}
}
