<?php
/**
 * Database Corruption Scan Diagnostic
 *
 * Performs a lightweight table integrity check on core tables.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Database_Corruption_Scan Class
 *
 * Runs CHECK TABLE on the posts table to detect corruption.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Database_Corruption_Scan extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-corruption-scan';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Corruption Scan';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for database table corruption';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$table = $wpdb->posts;
		$result = $wpdb->get_row( "CHECK TABLE {$table} QUICK" );

		if ( $result && isset( $result->Msg_text ) && 'OK' !== $result->Msg_text ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: message text */
					__( 'Database integrity check reported: %s', 'wpshadow' ),
					esc_html( $result->Msg_text )
				),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-corruption-scan?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'table'    => $table,
					'message'  => $result->Msg_text,
				),
			);
		}

		return null;
	}
}