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
use WPShadow\Core\KPI_Tracker;
use WPShadow\Treatments\Treatment_Registry;
use WPShadow\Diagnostics\Diagnostic_Registry;
use WPShadow\Workflow\Workflow_Manager;
use WPShadow\Privacy\Consent_Preferences;

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
		$limit   = isset( $assoc_args['limit'] ) ? (int) $assoc_args['limit'] : 20;
		$filters = array();

		if ( isset( $assoc_args['category'] ) ) {
			$filters['category'] = sanitize_key( $assoc_args['category'] );
		}
		if ( isset( $assoc_args['action'] ) ) {
			$filters['action'] = sanitize_key( $assoc_args['action'] );
		}

		$result = Activity_Logger::get_activities( $filters, $limit, 0 );
		$items  = array_map(
			function ( $entry ) {
				return array(
					'date'     => $entry['date'],
					'action'   => $entry['action'],
					'category' => $entry['category'],
					'details'  => $entry['details'],
					'user'     => $entry['user_name'],
				);
			},
			$result['activities']
		);

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

	/**
	 * List available treatments.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Output format (table, json, csv, yaml). Default: table
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpshadow treatment list
	 *     wp wpshadow treatment list --format=json
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function treatment_list( array $args, array $assoc_args ): void {
		$format = $assoc_args['format'] ?? 'table';

		$treatments = Treatment_Registry::get_all();
		$data       = array();

		foreach ( $treatments as $class ) {
			if ( ! method_exists( $class, 'get_name' ) ) {
				continue;
			}

			$data[] = array(
				'class'       => $class,
				'name'        => $class::get_name(),
				'description' => method_exists( $class, 'get_description' ) ? wp_strip_all_tags( $class::get_description() ) : '',
			);
		}

		$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'class', 'name', 'description' ) );
		$formatter->display_items( $data );
	}

	/**
	 * Apply a treatment to a finding.
	 *
	 * ## OPTIONS
	 *
	 * <finding_id>
	 * : The finding ID to apply treatment to
	 *
	 * [--dry-run]
	 * : Show what would be done without applying changes
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpshadow treatment apply ssl-check
	 *     wp wpshadow treatment apply ssl-check --dry-run
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function treatment_apply( array $args, array $assoc_args ): void {
		$finding_id = $args[0];
		$dry_run    = isset( $assoc_args['dry-run'] );

		// Find matching treatment.
		$treatments = Treatment_Registry::get_all();
		$matched    = null;

		foreach ( $treatments as $class ) {
			if ( method_exists( $class, 'get_slug' ) && $class::get_slug() === $finding_id ) {
				$matched = $class;
				break;
			}
		}

		if ( ! $matched ) {
			WP_CLI::error( "No treatment found for finding: {$finding_id}" );
			return;
		}

		if ( $dry_run ) {
			WP_CLI::line( "Would apply treatment: {$matched::get_name()}" );
			WP_CLI::line( 'Description: ' . wp_strip_all_tags( $matched::get_description() ) );
			return;
		}

		// Apply via execute() wrapper (includes hooks).
		if ( method_exists( $matched, 'execute' ) ) {
			$result = $matched::execute();
		} else {
			$result = $matched::apply();
		}

		if ( $result ) {
			WP_CLI::success( "Treatment applied: {$matched::get_name()}" );
		} else {
			WP_CLI::error( "Treatment failed: {$matched::get_name()}" );
		}
	}

	/**
	 * List workflows.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Output format (table, json, csv, yaml). Default: table
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpshadow workflow list
	 *     wp wpshadow workflow list --format=json
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function workflow_list( array $args, array $assoc_args ): void {
		$format    = $assoc_args['format'] ?? 'table';
		$workflows = Workflow_Manager::get_workflows();
		$data      = array();

		foreach ( $workflows as $id => $workflow ) {
			$data[] = array(
				'id'      => $id,
				'name'    => $workflow['name'] ?? 'Unnamed',
				'enabled' => isset( $workflow['enabled'] ) && $workflow['enabled'] ? 'yes' : 'no',
			);
		}

		$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'id', 'name', 'enabled' ) );
		$formatter->display_items( $data );
	}

	/**
	 * Toggle a workflow on/off.
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : Workflow ID
	 *
	 * [--enable]
	 * : Enable the workflow
	 *
	 * [--disable]
	 * : Disable the workflow
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpshadow workflow toggle my-workflow --enable
	 *     wp wpshadow workflow toggle my-workflow --disable
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function workflow_toggle( array $args, array $assoc_args ): void {
		$id = $args[0];

		if ( isset( $assoc_args['enable'] ) ) {
			$enabled = true;
		} elseif ( isset( $assoc_args['disable'] ) ) {
			$enabled = false;
		} else {
			WP_CLI::error( 'Must specify --enable or --disable' );
			return;
		}

		$result = Workflow_Manager::toggle_workflow( $id, $enabled );

		if ( $result ) {
			$status = $enabled ? 'enabled' : 'disabled';
			WP_CLI::success( "Workflow {$status}: {$id}" );
		} else {
			WP_CLI::error( "Failed to toggle workflow: {$id}" );
		}
	}

	/**
	 * Show KPI summary.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Output format (table, json, yaml). Default: table
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpshadow kpi summary
	 *     wp wpshadow kpi summary --format=json
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function kpi_summary( array $args, array $assoc_args ): void {
		$format  = $assoc_args['format'] ?? 'table';
		$summary = KPI_Tracker::get_kpi_summary();

		if ( 'json' === $format ) {
			WP_CLI::line( wp_json_encode( $summary, JSON_PRETTY_PRINT ) );
		} elseif ( 'yaml' === $format ) {
			foreach ( $summary as $key => $value ) {
				WP_CLI::line( "{$key}: {$value}" );
			}
		} else {
			// Table format.
			$data = array();
			foreach ( $summary as $key => $value ) {
				$data[] = array(
					'metric' => $key,
					'value'  => $value,
				);
			}
			$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'metric', 'value' ) );
			$formatter->display_items( $data );
		}
	}

	/**
	 * Get consent preferences.
	 *
	 * ## OPTIONS
	 *
	 * [--user=<user_id>]
	 * : User ID (default: current user in cron context, or user 1)
	 *
	 * [--format=<format>]
	 * : Output format (table, json, yaml). Default: table
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpshadow consent get
	 *     wp wpshadow consent get --user=2 --format=json
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function consent_get( array $args, array $assoc_args ): void {
		$user_id = isset( $assoc_args['user'] ) ? (int) $assoc_args['user'] : 1;
		$format  = $assoc_args['format'] ?? 'table';

		$preferences = Consent_Preferences::get_preferences( $user_id );

		if ( 'json' === $format ) {
			WP_CLI::line( wp_json_encode( $preferences, JSON_PRETTY_PRINT ) );
		} elseif ( 'yaml' === $format ) {
			foreach ( $preferences as $key => $value ) {
				$display = is_bool( $value ) ? ( $value ? 'true' : 'false' ) : $value;
				WP_CLI::line( "{$key}: {$display}" );
			}
		} else {
			// Table format.
			$data = array();
			foreach ( $preferences as $key => $value ) {
				$data[] = array(
					'preference' => $key,
					'value'      => is_bool( $value ) ? ( $value ? 'yes' : 'no' ) : $value,
				);
			}
			$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'preference', 'value' ) );
			$formatter->display_items( $data );
		}
	}
}

WP_CLI::add_command( 'wpshadow', WPShadow_CLI::class );
