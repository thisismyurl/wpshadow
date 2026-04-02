<?php
/**
 * Database Size Diagnostic
 *
 * Checks the total database size and flags unusually large databases.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Database_Size Class
 *
 * Estimates database size using information_schema.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Database_Size extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-size';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Size';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the database size is unusually large';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$size = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = %s",
				$wpdb->dbname
			)
		);

		if ( null === $size ) {
			return null;
		}

		$size = (int) $size;
		$size_mb = round( $size / 1024 / 1024, 2 );

		if ( $size_mb > 1024 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database is larger than 1GB. Large databases can slow backups and queries.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-size',
				'meta'         => array(
					'size_mb' => $size_mb,
				),
			);
		}

		if ( $size_mb > 500 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database size is growing. Consider cleanup of revisions, transients, and logs.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-size',
				'meta'         => array(
					'size_mb' => $size_mb,
				),
			);
		}

		return null;
	}
}