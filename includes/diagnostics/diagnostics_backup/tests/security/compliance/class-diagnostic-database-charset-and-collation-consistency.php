<?php
/**
 * Database Charset and Collation Consistency Diagnostic
 *
 * Validates database charset/collation is utf8mb4 across all tables.
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
 * Database Charset and Collation Consistency Class
 *
 * Tests database charset.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Database_Charset_And_Collation_Consistency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-charset-and-collation-consistency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Charset and Collation Consistency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates database charset/collation is utf8mb4 across all tables';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$charset_check = self::check_charset_consistency();
		
		if ( $charset_check['has_issues'] ) {
			$issues = array();
			
			if ( $charset_check['tables_with_wrong_charset'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of tables */
					__( '%d tables using old utf8 charset (should be utf8mb4)', 'wpshadow' ),
					$charset_check['tables_with_wrong_charset']
				);
			}

			if ( $charset_check['config_mismatch'] ) {
				$issues[] = __( 'DB_CHARSET in wp-config.php does not match database charset', 'wpshadow' );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-charset-and-collation-consistency',
				'meta'         => array(
					'total_tables'                => $charset_check['total_tables'],
					'tables_with_wrong_charset'   => $charset_check['tables_with_wrong_charset'],
					'config_mismatch'             => $charset_check['config_mismatch'],
					'database_charset'            => $charset_check['database_charset'],
				),
			);
		}

		return null;
	}

	/**
	 * Check charset consistency.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_charset_consistency() {
		global $wpdb;

		$check = array(
			'has_issues'                => false,
			'total_tables'              => 0,
			'tables_with_wrong_charset' => 0,
			'config_mismatch'           => false,
			'database_charset'          => '',
		);

		// Get database charset.
		$charset_result = $wpdb->get_var( "SHOW VARIABLES LIKE 'character_set_database'" );
		if ( $charset_result ) {
			$check['database_charset'] = $wpdb->get_var( "SHOW VARIABLES WHERE Variable_name = 'character_set_database'" );
		}

		// Get all tables.
		$tables = $wpdb->get_results( "SHOW TABLE STATUS" );
		
		foreach ( $tables as $table ) {
			++$check['total_tables'];

			// Check if table uses old utf8 instead of utf8mb4.
			if ( isset( $table->Collation ) ) {
				if ( 0 === strpos( $table->Collation, 'utf8_' ) && 
				     0 !== strpos( $table->Collation, 'utf8mb4_' ) ) {
					++$check['tables_with_wrong_charset'];
					$check['has_issues'] = true;
				}
			}
		}

		// Check DB_CHARSET constant.
		if ( defined( 'DB_CHARSET' ) ) {
			if ( 'utf8mb4' !== DB_CHARSET && ! empty( $check['database_charset'] ) ) {
				if ( DB_CHARSET !== $check['database_charset'] ) {
					$check['config_mismatch'] = true;
					$check['has_issues'] = true;
				}
			}
		}

		return $check;
	}
}
