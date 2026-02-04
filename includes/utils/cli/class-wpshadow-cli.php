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

	/**
	 * List available diagnostics.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Output format (table, json, csv, yaml). Default: table
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpshadow diagnostic list
	 *     wp wpshadow diagnostic list --format=json
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function diagnostic_list( array $args, array $assoc_args ): void {
		$format      = $assoc_args['format'] ?? 'table';
		$diagnostics = Diagnostic_Registry::get_all();
		$data        = array();

		foreach ( $diagnostics as $class ) {
			if ( ! method_exists( $class, 'get_name' ) ) {
				continue;
			}

			$data[] = array(
				'class' => $class,
				'name'  => $class::get_name(),
			);
		}

		$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'class', 'name' ) );
		$formatter->display_items( $data );
	}

	/**
	 * Run a diagnostic check.
	 *
	 * ## OPTIONS
	 *
	 * [<diagnostic_class>]
	 * : The diagnostic class name to run (e.g., 'Diagnostic_SSL'). If not provided, runs all diagnostics.
	 *
	 * [--format=<format>]
	 * : Output format (table, json, yaml). Default: table
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpshadow diagnostic run
	 *     wp wpshadow diagnostic run Diagnostic_SSL
	 *     wp wpshadow diagnostic run --format=json
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function diagnostic_run( array $args, array $assoc_args ): void {
		$format      = $assoc_args['format'] ?? 'table';
		$diagnostics = Diagnostic_Registry::get_all();

		// If specific diagnostic requested, filter to that one.
		if ( isset( $args[0] ) ) {
			$target_class = $args[0];
			// Try to match by class name or partial name.
			$diagnostics = array_filter(
				$diagnostics,
				function ( $class ) use ( $target_class ) {
					return false !== stripos( $class, $target_class );
				}
			);

			if ( empty( $diagnostics ) ) {
				WP_CLI::error( "No diagnostic found matching: {$target_class}" );
				return;
			}
		}

		$results = array();

		foreach ( $diagnostics as $class ) {
			if ( ! method_exists( $class, 'check' ) ) {
				continue;
			}

			$finding = $class::check();

			if ( $finding ) {
				$results[] = array(
					'diagnostic' => $class::get_name(),
					'status'     => $finding['status'] ?? 'unknown',
					'severity'   => $finding['severity'] ?? 'info',
					'message'    => isset( $finding['message'] ) ? wp_strip_all_tags( $finding['message'] ) : '',
				);
			}
		}

		if ( empty( $results ) ) {
			WP_CLI::success( 'All diagnostics passed!' );
			return;
		}

		if ( 'json' === $format ) {
			WP_CLI::line( wp_json_encode( $results, JSON_PRETTY_PRINT ) );
		} elseif ( 'yaml' === $format ) {
			foreach ( $results as $result ) {
				WP_CLI::line( '---' );
				foreach ( $result as $key => $value ) {
					WP_CLI::line( "{$key}: {$value}" );
				}
			}
		} else {
			$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'diagnostic', 'status', 'severity', 'message' ) );
			$formatter->display_items( $results );
		}
	}

	/**
	 * Undo a treatment.
	 *
	 * ## OPTIONS
	 *
	 * <finding_id>
	 * : The finding ID to undo treatment for
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpshadow treatment undo ssl-check
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function treatment_undo( array $args, array $assoc_args ): void {
		$finding_id = $args[0];

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

		if ( ! method_exists( $matched, 'undo' ) ) {
			WP_CLI::error( "Treatment does not support undo: {$matched::get_name()}" );
			return;
		}

		// Use execute_undo() wrapper if available to fire hooks, otherwise call undo() directly.
		if ( method_exists( $matched, 'execute_undo' ) ) {
			$result = $matched::execute_undo();
		} else {
			$result = $matched::undo();
		}

		if ( $result && ( ! is_array( $result ) || ! empty( $result['success'] ) ) ) {
			WP_CLI::success( "Treatment undone: {$matched::get_name()}" );
		} else {
			$message = is_array( $result ) && isset( $result['message'] ) ? $result['message'] : 'Unknown error';
			WP_CLI::error( "Treatment undo failed: {$matched::get_name()} - {$message}" );
		}
	}

	/**
	 * Get or set plugin settings.
	 *
	 * ## OPTIONS
	 *
	 * [<setting_name>]
	 * : Setting name to get or set
	 *
	 * [<setting_value>]
	 * : Setting value to set (if not provided, gets current value)
	 *
	 * [--format=<format>]
	 * : Output format (table, json, yaml). Default: table
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpshadow setting list
	 *     wp wpshadow setting get wpshadow_auto_fix_enabled
	 *     wp wpshadow setting set wpshadow_auto_fix_enabled 1
	 *     wp wpshadow setting list --format=json
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function setting( array $args, array $assoc_args ): void {
		$format = $assoc_args['format'] ?? 'table';

		// If no setting name provided, list all settings.
		if ( empty( $args ) ) {
			$this->list_settings( $format, $assoc_args );
			return;
		}

		$setting_name = $args[0];

		// If value provided, set the setting.
		if ( isset( $args[1] ) ) {
			$setting_value = $args[1];
			update_option( $setting_name, $setting_value );
			WP_CLI::success( "Setting updated: {$setting_name} = {$setting_value}" );
			return;
		}

		// Get the setting.
		$value = get_option( $setting_name, null );

		if ( null === $value ) {
			WP_CLI::warning( "Setting not found: {$setting_name}" );
			return;
		}

		if ( 'json' === $format ) {
			WP_CLI::line( wp_json_encode( array( $setting_name => $value ), JSON_PRETTY_PRINT ) );
		} else {
			WP_CLI::line( "{$setting_name}: " . ( is_bool( $value ) ? ( $value ? 'true' : 'false' ) : $value ) );
		}
	}

	/**
	 * List all WPShadow settings.
	 *
	 * @param string $format       Output format.
	 * @param array  $assoc_args   Associative arguments.
	 */
	private function list_settings( string $format, array $assoc_args ): void {
		// Use native WordPress function to load all options
		$all_options = wp_load_alloptions();

		// Filter for wpshadow options only
		$settings = array_filter(
			$all_options,
			function ( $key ) {
				return str_starts_with( $key, 'wpshadow_' );
			},
			ARRAY_FILTER_USE_KEY
		);

		if ( empty( $settings ) ) {
			WP_CLI::warning( 'No WPShadow settings found.' );
			return;
		}

		// Sort by key for consistent output
		ksort( $settings );

		$data = array();
		foreach ( $settings as $name => $value ) {
			$data[] = array(
				'name'  => $name,
				'value' => $value,
			);
		}

		if ( 'json' === $format ) {
			WP_CLI::line( wp_json_encode( $data, JSON_PRETTY_PRINT ) );
		} elseif ( 'yaml' === $format ) {
			foreach ( $data as $item ) {
				WP_CLI::line( "{$item['name']}: {$item['value']}" );
			}
		} else {
			$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'name', 'value' ) );
			$formatter->display_items( $data );
		}
	}
}

WP_CLI::add_command( 'wpshadow', WPShadow_CLI::class );
