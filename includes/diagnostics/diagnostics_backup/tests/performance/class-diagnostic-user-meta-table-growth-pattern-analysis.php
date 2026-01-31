<?php
/**
 * User Meta Table Growth Pattern Analysis Diagnostic
 *
 * Identifies plugins creating excessive usermeta rows per user.
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
 * User Meta Table Growth Pattern Analysis Class
 *
 * Tests usermeta growth patterns.
 *
 * @since 1.26029.0000
 */
class Diagnostic_User_Meta_Table_Growth_Pattern_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-meta-table-growth-pattern-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Meta Table Growth Pattern Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies plugins creating excessive usermeta rows per user';

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
		$usermeta_check = self::check_usermeta_growth();
		
		if ( $usermeta_check['avg_rows_per_user'] > 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: average usermeta rows per user */
					__( 'Average %d usermeta rows per user (excessive plugin bloat detected)', 'wpshadow' ),
					$usermeta_check['avg_rows_per_user']
				),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-meta-table-growth-pattern-analysis',
				'meta'         => array(
					'avg_rows_per_user' => $usermeta_check['avg_rows_per_user'],
					'total_meta_rows'   => $usermeta_check['total_meta_rows'],
					'user_count'        => $usermeta_check['user_count'],
					'top_meta_keys'     => $usermeta_check['top_meta_keys'],
				),
			);
		}

		return null;
	}

	/**
	 * Check usermeta growth patterns.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_usermeta_growth() {
		global $wpdb;

		$check = array(
			'avg_rows_per_user' => 0,
			'total_meta_rows'   => 0,
			'user_count'        => 0,
			'top_meta_keys'     => array(),
		);

		// Count total usermeta rows.
		$check['total_meta_rows'] = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta}"
		);

		// Count total users.
		$check['user_count'] = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->users}"
		);

		// Calculate average.
		if ( $check['user_count'] > 0 ) {
			$check['avg_rows_per_user'] = (int) ( $check['total_meta_rows'] / $check['user_count'] );
		}

		// Get top meta keys by count.
		$top_keys = $wpdb->get_results(
			"SELECT 
				meta_key,
				COUNT(*) as count,
				AVG(LENGTH(meta_value)) as avg_size
			FROM {$wpdb->usermeta}
			GROUP BY meta_key
			ORDER BY count DESC
			LIMIT 10",
			ARRAY_A
		);

		if ( ! empty( $top_keys ) ) {
			foreach ( $top_keys as $key_data ) {
				$check['top_meta_keys'][] = array(
					'meta_key' => $key_data['meta_key'],
					'count'    => (int) $key_data['count'],
					'avg_size' => (int) $key_data['avg_size'],
				);
			}
		}

		return $check;
	}
}
