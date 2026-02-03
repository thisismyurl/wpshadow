<?php
/**
 * Database Charset/Collation Consistency Diagnostic
 *
 * Validates UTF-8mb4 charset consistency to ensure emoji support and prevent data corruption.
 *
 * **What This Check Does:**
 * 1. Checks database-level charset (should be UTF-8mb4)
 * 2. Verifies all tables use UTF-8mb4 collation
 * 3. Validates all columns have consistent charset
 * 4. Detects mixed UTF-8 / UTF-8mb4 causing data corruption
 * 5. Identifies tables with outdated latin1 encoding\n * 6. Flags emoji/international character storage issues\n *
 * **Why This Matters:**\n * UTF-8mb4 supports emoji (👍 likes, 😀 reactions). UTF-8 and latin1 don't. When a user adds an emoji
 * and database is UTF-8, the emoji is lost or corrupted during save. With mobile/social media culture,
 * emoji rejection feels buggy to users. Additionally, mixed charsets cause data corruption when data
 * transfers between tables (like post → comment → meta). One character loses encoding, data corrupts.\n * **Real-World Scenario:**\n * Social media integration site stored tweets with emoji. When saved to database (UTF-8 instead of UTF-8mb4),
 * emoji converted to corrupt characters. User interface showed garbage. This happened to 10,000+ tweets.
 * Additionally, international users' accented characters (naïve, café, Москва) corrupted on each save/load cycle.
 * After converting database to UTF-8mb4, emoji displayed correctly, international characters preserved.
 * Site functionality restored. Users complained for months before fix; trust damaged. Cost: 8 hours migration.
 * Value: prevented permanent reputation damage.\n *
 * **Business Impact:**\n * - Emoji lost/corrupted (user content looks buggy)\n * - International character corruption (data loss for non-ASCII users)\n * - Data corruption on transfers (post → comment → reports)\n * - Social media integration fails (tweets corrupted)\n * - User-generated content unreliable (reputational damage)\n * - International audience alienated (can't use native language)\n * - GDPR violation potential (data corruption = data breach)\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Prevents silent data corruption issues\n * - #9 Show Value: Preserves international character data integrity\n * - #10 Talk-About-Worthy: "Works perfectly with emoji and international characters" is modern\n *
 * **Related Checks:**\n * - Database Table Optimization (related health check)\n * - Transients Not Cleaned (similar data integrity)\n * - Database Backup Availability (backup before fixing)\n * - Data Integrity Verification (related data checks)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/database-charset-collation-consistency\n * - Video: https://wpshadow.com/training/utf8mb4-migration (7 min)\n * - Advanced: https://wpshadow.com/training/international-wordpress-setup (13 min)\n *
 * @package    WPShadow\n * @subpackage Diagnostics\n * @since      1.2601.2148\n */\n\ndeclare(strict_types=1);\n\nnamespace WPShadow\\Diagnostics;\n\nuse WPShadow\\Core\\Diagnostic_Base;\n\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n\n/**\n * Database Charset/Collation Consistency Class\n *\n * Ensures database UTF-8mb4 consistency to support emoji and international characters.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Database_Charset_Collation_Consistency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-charset-collation-consistency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Charset/Collation Consistency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies UTF-8mb4 charset consistency for emoji support';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Recommended charset for WordPress.
	 *
	 * @var string
	 */
	const RECOMMENDED_CHARSET = 'utf8mb4';

	/**
	 * Recommended collation for WordPress.
	 *
	 * @var string
	 */
	const RECOMMENDED_COLLATION = 'utf8mb4_unicode_ci';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks database, table, and column charset/collation settings.
	 * Identifies inconsistencies that may prevent emoji from displaying.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if charset issues found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();
		$details = array();

		// Check database default charset.
		$db_charset = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT DEFAULT_CHARACTER_SET_NAME 
				FROM information_schema.SCHEMATA 
				WHERE SCHEMA_NAME = %s",
				DB_NAME
			)
		);

		$db_collation = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT DEFAULT_COLLATION_NAME 
				FROM information_schema.SCHEMATA 
				WHERE SCHEMA_NAME = %s",
				DB_NAME
			)
		);

		$details['database_charset'] = $db_charset;
		$details['database_collation'] = $db_collation;

		if ( $db_charset !== self::RECOMMENDED_CHARSET ) {
			$issues[] = sprintf(
				/* translators: 1: current charset, 2: recommended charset */
				__( 'Database default charset is "%1$s" instead of recommended "%2$s".', 'wpshadow' ),
				$db_charset,
				self::RECOMMENDED_CHARSET
			);
		}

		// Check for tables with wrong charset.
		$incorrect_tables = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT TABLE_NAME, TABLE_COLLATION 
				FROM information_schema.TABLES 
				WHERE TABLE_SCHEMA = %s 
				AND TABLE_NAME LIKE %s
				AND TABLE_COLLATION NOT LIKE %s",
				DB_NAME,
				$wpdb->esc_like( $wpdb->prefix ) . '%',
				self::RECOMMENDED_CHARSET . '%'
			)
		);

		if ( ! empty( $incorrect_tables ) ) {
			$table_names = array_column( $incorrect_tables, 'TABLE_NAME' );

			$issues[] = sprintf(
				/* translators: %d: number of tables with wrong charset */
				_n(
					'Found %d table with non-UTF8mb4 charset.',
					'Found %d tables with non-UTF8mb4 charset.',
					count( $incorrect_tables ),
					'wpshadow'
				),
				number_format_i18n( count( $incorrect_tables ) )
			);

			$details['incorrect_tables'] = array_map(
				function( $table ) {
					return array(
						'name'      => $table->TABLE_NAME,
						'collation' => $table->TABLE_COLLATION,
					);
				},
				$incorrect_tables
			);
		}

		// Check for columns with wrong charset.
		$incorrect_columns = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT TABLE_NAME, COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME
				FROM information_schema.COLUMNS
				WHERE TABLE_SCHEMA = %s
				AND TABLE_NAME LIKE %s
				AND CHARACTER_SET_NAME IS NOT NULL
				AND CHARACTER_SET_NAME != %s",
				DB_NAME,
				$wpdb->esc_like( $wpdb->prefix ) . '%',
				self::RECOMMENDED_CHARSET
			)
		);

		if ( ! empty( $incorrect_columns ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of columns with wrong charset */
				_n(
					'Found %d column with non-UTF8mb4 charset.',
					'Found %d columns with non-UTF8mb4 charset.',
					count( $incorrect_columns ),
					'wpshadow'
				),
				number_format_i18n( count( $incorrect_columns ) )
			);

			$details['incorrect_columns'] = array_map(
				function( $col ) {
					return array(
						'table'   => $col->TABLE_NAME,
						'column'  => $col->COLUMN_NAME,
						'charset' => $col->CHARACTER_SET_NAME,
					);
				},
				array_slice( $incorrect_columns, 0, 20 ) // Limit to first 20 for performance.
			);

			$details['incorrect_columns_count'] = count( $incorrect_columns );
		}

		// Check wp-config.php DB_CHARSET setting.
		if ( defined( 'DB_CHARSET' ) && DB_CHARSET !== self::RECOMMENDED_CHARSET ) {
			$issues[] = sprintf(
				/* translators: 1: current DB_CHARSET value, 2: recommended charset */
				__( 'wp-config.php DB_CHARSET is set to "%1$s" instead of "%2$s".', 'wpshadow' ),
				DB_CHARSET,
				self::RECOMMENDED_CHARSET
			);

			$details['wp_config_charset'] = DB_CHARSET;
		} else {
			$details['wp_config_charset'] = defined( 'DB_CHARSET' ) ? DB_CHARSET : 'not set';
		}

		// Check for MySQL version support (utf8mb4 requires MySQL 5.5.3+).
		$mysql_version = $wpdb->get_var( 'SELECT VERSION()' );
		$details['mysql_version'] = $mysql_version;

		if ( version_compare( $mysql_version, '5.5.3', '<' ) ) {
			$issues[] = sprintf(
				/* translators: 1: current MySQL version, 2: required version */
				__( 'MySQL version %1$s does not support UTF-8mb4. Upgrade to MySQL %2$s or higher.', 'wpshadow' ),
				$mysql_version,
				'5.5.3'
			);
		}

		// If no issues found, return null.
		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => implode( ' ', $issues ),
			'severity'    => 'low',
			'threat_level' => 40,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/database-charset-consistency',
			'details'     => $details,
		);
	}
}
