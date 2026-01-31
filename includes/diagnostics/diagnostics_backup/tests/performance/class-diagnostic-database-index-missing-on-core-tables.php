<?php
/**
 * Database Index Missing on Core Tables Diagnostic
 *
 * Validates critical database indexes exist for performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Index Missing on Core Tables Class
 *
 * Tests database indexes.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Database_Index_Missing_On_Core_Tables extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-index-missing-on-core-tables';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Index Missing on Core Tables';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates critical database indexes exist for performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$index_check = self::check_database_indexes();
		
		if ( ! empty( $index_check['missing_indexes'] ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of missing indexes */
					__( 'Missing %d critical database indexes (queries 100-1000x slower)', 'wpshadow' ),
					count( $index_check['missing_indexes'] )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-index-missing-on-core-tables',
				'meta'         => array(
					'missing_indexes' => $index_check['missing_indexes'],
				),
			);
		}

		return null;
	}

	/**
	 * Check database indexes.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_database_indexes() {
		global $wpdb;

		$check = array(
			'missing_indexes' => array(),
		);

		// Critical indexes to check.
		$required_indexes = array(
			$wpdb->posts => array(
				'type_status_date' => array( 'post_type', 'post_status', 'post_date' ),
			),
			$wpdb->postmeta => array(
				'meta_key' => array( 'meta_key' ),
			),
			$wpdb->options => array(
				'autoload' => array( 'autoload' ),
			),
			$wpdb->comments => array(
				'comment_approved_date_gmt' => array( 'comment_approved', 'comment_date_gmt' ),
			),
		);

		foreach ( $required_indexes as $table => $indexes ) {
			// Get existing indexes for this table.
			$existing_indexes = $wpdb->get_results(
				$wpdb->prepare( 'SHOW INDEX FROM %i', $table ),
				ARRAY_A
			);

			if ( empty( $existing_indexes ) ) {
				continue;
			}

			// Build map of existing index columns.
			$index_map = array();
			foreach ( $existing_indexes as $index ) {
				$key_name = $index['Key_name'];
				if ( ! isset( $index_map[ $key_name ] ) ) {
					$index_map[ $key_name ] = array();
				}
				$index_map[ $key_name ][] = $index['Column_name'];
			}

			// Check if required indexes exist.
			foreach ( $indexes as $index_name => $columns ) {
				$found = false;
				
				foreach ( $index_map as $existing_columns ) {
					// Check if all required columns are in the index.
					$match = true;
					foreach ( $columns as $col ) {
						if ( ! in_array( $col, $existing_columns, true ) ) {
							$match = false;
							break;
						}
					}
					
					if ( $match ) {
						$found = true;
						break;
					}
				}

				if ( ! $found ) {
					$check['missing_indexes'][] = array(
						'table'   => str_replace( $wpdb->prefix, 'wp_', $table ),
						'index'   => $index_name,
						'columns' => $columns,
					);
				}
			}
		}

		return $check;
	}
}
