<?php
/**
 * wp_posts Table Fragmentation Level Diagnostic
 *
 * Measures wp_posts table fragmentation requiring optimization.
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
 * wp_posts Table Fragmentation Level Class
 *
 * Tests table fragmentation.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Wp_Posts_Table_Fragmentation_Level extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wp-posts-table-fragmentation-level';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'wp_posts Table Fragmentation Level';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures wp_posts table fragmentation requiring optimization';

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
		$fragmentation_check = self::check_table_fragmentation();
		
		if ( $fragmentation_check['is_fragmented'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: fragmentation percentage, 2: wasted space in MB */
					__( 'wp_posts table is %1$s%% fragmented (%2$sMB wasted space)', 'wpshadow' ),
					number_format( $fragmentation_check['fragmentation_percent'], 1 ),
					number_format( $fragmentation_check['data_free'] / 1048576, 2 )
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp-posts-table-fragmentation-level',
				'meta'         => array(
					'data_length'           => $fragmentation_check['data_length'],
					'data_free'             => $fragmentation_check['data_free'],
					'fragmentation_percent' => $fragmentation_check['fragmentation_percent'],
					'table_engine'          => $fragmentation_check['engine'],
				),
			);
		}

		return null;
	}

	/**
	 * Check table fragmentation.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_table_fragmentation() {
		global $wpdb;

		$check = array(
			'is_fragmented'         => false,
			'data_length'           => 0,
			'data_free'             => 0,
			'fragmentation_percent' => 0,
			'engine'                => '',
		);

		// Get table status.
		$table_status = $wpdb->get_row(
			$wpdb->prepare(
				'SHOW TABLE STATUS WHERE Name = %s',
				$wpdb->posts
			),
			ARRAY_A
		);

		if ( ! $table_status ) {
			return $check;
		}

		$check['data_length'] = (int) $table_status['Data_length'];
		$check['data_free'] = (int) $table_status['Data_free'];
		$check['engine'] = $table_status['Engine'];

		// Calculate fragmentation percentage.
		if ( $check['data_length'] > 0 ) {
			$check['fragmentation_percent'] = ( $check['data_free'] / ( $check['data_length'] + $check['data_free'] ) ) * 100;
		}

		// Flag as fragmented if >20% fragmentation OR >10MB wasted space.
		if ( $check['fragmentation_percent'] > 20 || $check['data_free'] > 10485760 ) {
			$check['is_fragmented'] = true;
		}

		return $check;
	}
}
