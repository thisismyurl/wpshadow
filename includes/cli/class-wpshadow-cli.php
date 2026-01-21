<?php
/**
 * WP-CLI commands for WPShadow
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\CLI;

use WP_CLI;
use WP_CLI_Command;
use WPShadow\Core\Activity_Logger;

if ( ! class_exists( '\WP_CLI' ) ) {
	exit;
}

/**
 * WPShadow command namespace.
 */
class WPShadow_CLI extends WP_CLI_Command {
	/**
	 * List recent activity entries.
	 *
	 * ## OPTIONS
	 *
	 * [--category=<category>]
	 * : Filter by category slug.
	 *
	 * [--action=<action>]
	 * : Filter by action key.
	 *
	 * [--limit=<number>]
	 * : Number of entries to return. Default 20.
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpshadow activity list --limit=10
	 *     wp wpshadow activity list --category=security
	 */
	public function activity_list( array $args, array $assoc_args ) {
		$limit    = isset( $assoc_args['limit'] ) ? (int) $assoc_args['limit'] : 20;
		$filters  = array();

		if ( isset( $assoc_args['category'] ) ) {
			$filters['category'] = sanitize_key( $assoc_args['category'] );
		}
		if ( isset( $assoc_args['action'] ) ) {
			$filters['action'] = sanitize_key( $assoc_args['action'] );
		}

		$result = Activity_Logger::get_activities( $filters, $limit, 0 );
		$items  = array_map( function( $entry ) {
			return array(
				'date'     => $entry['date'],
				'action'   => $entry['action'],
				'category' => $entry['category'],
				'details'  => $entry['details'],
				'user'     => $entry['user_name'],
			);
		}, $result['activities'] );

		if ( empty( $items ) ) {
			WP_CLI::success( 'No activity found.' );
			return;
		}

		$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'date', 'action', 'category', 'details', 'user' ) );
		$formatter->display_items( $items );
	}

	/**
	 * Export activity log to CSV.
	 *
	 * ## OPTIONS
	 *
	 * [--category=<category>]
	 * : Filter by category slug.
	 *
	 * [--action=<action>]
	 * : Filter by action key.
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpshadow activity export > activity.csv
	 *     wp wpshadow activity export --category=security
	 */
	public function activity_export( array $args, array $assoc_args ) {
		$filters = array();

		if ( isset( $assoc_args['category'] ) ) {
			$filters['category'] = sanitize_key( $assoc_args['category'] );
		}
		if ( isset( $assoc_args['action'] ) ) {
			$filters['action'] = sanitize_key( $assoc_args['action'] );
		}

		$csv = Activity_Logger::export_csv( $filters );
		if ( empty( $csv ) ) {
			WP_CLI::warning( 'No activity to export.' );
			return;
		}

		WP_CLI::line( $csv );
	}
}

WP_CLI::add_command( 'wpshadow', WPShadow_CLI::class );
