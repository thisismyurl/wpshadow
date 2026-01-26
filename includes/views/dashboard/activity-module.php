<?php
/**
 * WPShadow Recent Activity Dashboard Module
 *
 * Handles activity display including:
 * - Recent activity fetching and filtering
 * - Activity history page rendering
 * - Time formatting with tooltips
 * - Activity Logger integration
 *
 * @package WPShadow
 * @subpackage Dashboard
 */

declare(strict_types=1);

use WPShadow\Core\Form_Param_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get recent activity for dashboard
 *
 * @return array Recent activity entries with action and timestamp
 */
function wpshadow_get_recent_activity(): array {
	if ( ! class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
		return array();
	}

	$recent = \WPShadow\Core\Activity_Logger::get_recent( 10 );

	$action_labels = array(
		'diagnostic_run'            => __( 'Diagnostic Run', 'wpshadow' ),
		'diagnostic_failed'         => __( 'Diagnostic Failed', 'wpshadow' ),
		'treatment_applied'         => __( 'Auto-fix Applied', 'wpshadow' ),
		'treatment_undone'          => __( 'Auto-fix Undone', 'wpshadow' ),
		'finding_status_change'     => __( 'Status Changed', 'wpshadow' ),
		'finding_dismissed'         => __( 'Finding Dismissed', 'wpshadow' ),
		'finding_excluded'          => __( 'Finding Excluded', 'wpshadow' ),
		'finding_action'            => __( 'Finding Activity', 'wpshadow' ),
		'finding_resolved'          => __( 'Finding Fixed', 'wpshadow' ),
		'workflow_created'          => __( 'Workflow Created', 'wpshadow' ),
		'workflow_executed'         => __( 'Workflow Executed', 'wpshadow' ),
		'workflow_enabled'          => __( 'Workflow Enabled', 'wpshadow' ),
		'workflow_disabled'         => __( 'Workflow Disabled', 'wpshadow' ),
		'workflow_saved'            => __( 'Workflow Saved', 'wpshadow' ),
		'workflow_deleted'          => __( 'Workflow Deleted', 'wpshadow' ),
		'guardian_enabled'          => __( 'Guardian Enabled', 'wpshadow' ),
		'guardian_disabled'         => __( 'Guardian Disabled', 'wpshadow' ),
		'cache_settings_changed'    => __( 'Cache Settings Changed', 'wpshadow' ),
		'cache_cleared'             => __( 'Cache Cleared', 'wpshadow' ),
		'consent_saved'             => __( 'Consent Saved', 'wpshadow' ),
		'settings_changed'          => __( 'Settings Changed', 'wpshadow' ),
		'site_settings_changed'     => __( 'Site Settings Changed', 'wpshadow' ),
		'activity_pruned'           => __( 'Activity Log Pruned', 'wpshadow' ),
		'retention_setting_updated' => __( 'Retention Setting Updated', 'wpshadow' ),
		'data_cleanup_completed'    => __( 'Data Cleanup Completed', 'wpshadow' ),
	);

	$activity = array();

	foreach ( $recent as $entry ) {
		$action      = $entry['action'] ?? '';
		$label_text  = $action_labels[ $action ] ?? ucwords( str_replace( '_', ' ', (string) $action ) );
		$details     = isset( $entry['details'] ) ? trim( (string) $entry['details'] ) : '';
		$description = $details ? $label_text . ': ' . $details : $label_text;
		$timestamp   = isset( $entry['timestamp'] ) ? (int) $entry['timestamp'] : current_time( 'timestamp' );

		$activity[] = array(
			'action'   => $description,
			'time'     => $timestamp,
			'category' => $entry['category'] ?? '',
		);
	}

	if ( empty( $activity ) ) {
		$activity[] = array(
			'action'   => __( 'WPShadow activated', 'wpshadow' ),
			'time'     => current_time( 'timestamp' ),
			'category' => '',
		);
	}

	return $activity;
}

/**
 * Format time as relative with tooltip for precise details
 *
 * @param int $timestamp Unix timestamp
 * @return string HTML with formatted time
 */
function wpshadow_format_time_with_tooltip( int $timestamp ): string {
	$now  = current_time( 'timestamp' );
	$diff = $now - $timestamp;

	if ( $diff < 60 ) {
		$relative = __( 'just now', 'wpshadow' );
	} elseif ( $diff < 3600 ) {
		$minutes  = (int) floor( $diff / 60 );
		$relative = sprintf(
			/* translators: %d: number of minutes */
			_n( '%d minute ago', '%d minutes ago', $minutes, 'wpshadow' ),
			$minutes
		);
	} elseif ( $diff < 86400 ) {
		$hours    = (int) floor( $diff / 3600 );
		$relative = sprintf(
			/* translators: %d: number of hours */
			_n( '%d hour ago', '%d hours ago', $hours, 'wpshadow' ),
			$hours
		);
	} else {
		$days     = (int) floor( $diff / 86400 );
		$relative = sprintf(
			/* translators: %d: number of days */
			_n( '%d day ago', '%d days ago', $days, 'wpshadow' ),
			$days
		);
	}

	$precise = wp_date( get_option( 'date_format', 'Y-m-d' ) . ' ' . get_option( 'time_format', 'H:i:s' ), $timestamp );

	return sprintf(
		'<span title="%s">%s</span>',
		esc_attr( $precise ),
		esc_html( $relative )
	);
}

/**
 * Render recent activity section on dashboard
 *
 * Called via wpshadow_dashboard_activity hook
 */
function wpshadow_render_recent_activity(): void {
	$activity = wpshadow_get_recent_activity();

	if ( empty( $activity ) ) {
		return; // No activity to display
	}

	// Get category metadata for icons
	$category_meta = \WPShadow\Core\wpshadow_get_category_metadata();
	?>
	<div class="wps-activity-section wps-mt-8">
		<div class="wps-activity-header">
			<h2 class="wps-activity-title"><?php esc_html_e( 'Recent Activity', 'wpshadow' ); ?></h2>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-activity' ) ); ?>" class="wps-activity-link">
				<?php esc_html_e( 'View All Activity', 'wpshadow' ); ?>
				<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
			</a>
		</div>
		<div class="wps-activity-timeline" role="list" aria-label="<?php esc_attr_e( 'Recent site activity', 'wpshadow' ); ?>">
			<?php
			$count = 0;
			foreach ( $activity as $entry ) :
				if ( $count >= 7 ) {
					break; // Show only first 7 activities
				}
				++$count;

				// Determine icon based on action type
				$icon_class = 'dashicons-yes-alt';
				$icon_color = 'var(--wps-success)';

				if ( strpos( $entry['action'], 'Failed' ) !== false || strpos( $entry['action'], 'Error' ) !== false ) {
					$icon_class = 'dashicons-warning';
					$icon_color = 'var(--wps-danger)';
				} elseif ( strpos( $entry['action'], 'Scan' ) !== false || strpos( $entry['action'], 'Diagnostic' ) !== false ) {
					$icon_class = 'dashicons-update';
					$icon_color = 'var(--wps-info)';
				} elseif ( strpos( $entry['action'], 'Applied' ) !== false || strpos( $entry['action'], 'Fixed' ) !== false ) {
					$icon_class = 'dashicons-yes';
					$icon_color = 'var(--wps-success)';
				} elseif ( strpos( $entry['action'], 'Workflow' ) !== false ) {
					$icon_class = 'dashicons-controls-play';
					$icon_color = 'var(--wps-primary)';
				} elseif ( strpos( $entry['action'], 'Settings' ) !== false ) {
					$icon_class = 'dashicons-admin-settings';
					$icon_color = 'var(--wps-gray-600)';
				}
				?>
				<div class="wps-activity-item" role="listitem">
					<div class="wps-activity-icon" style="color: <?php echo esc_attr( $icon_color ); ?>;" aria-hidden="true">
						<span class="dashicons <?php echo esc_attr( $icon_class ); ?>"></span>
					</div>
					<div class="wps-activity-content">
						<div class="wps-activity-text"><?php echo esc_html( $entry['action'] ); ?></div>
						<time class="wps-activity-time" datetime="<?php echo esc_attr( gmdate( 'c', $entry['time'] ) ); ?>">
							<?php echo wp_kses_post( wpshadow_format_time_with_tooltip( $entry['time'] ) ); ?>
						</time>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Render activity history page
 *
 * Handles:
 * - Activity display with filtering
 * - CSV export functionality
 * - Activity Logger integration
 */
function wpshadow_render_activity_page(): void {
	if ( ! current_user_can( 'read' ) ) {
		wp_die( __( 'Insufficient permissions.', 'wpshadow' ) );
	}

	// Handle CSV export
	$export = Form_Param_Helper::get( 'export', 'text', '' );
	if ( $export === 'csv' ) {
		// Build filters for export
		$filters = array();
		$category = Form_Param_Helper::get( 'activity_category', 'key', '' );
		if ( ! empty( $category ) ) {
			$filters['category'] = $category;
		}
		$action = Form_Param_Helper::get( 'activity_action', 'key', '' );
		if ( ! empty( $action ) ) {
			$filters['action'] = $action;
		}
		$search = Form_Param_Helper::get( 'activity_search', 'text', '' );
		if ( ! empty( $search ) ) {
			$filters['search'] = $search;
		}

		// Generate CSV
		$csv = \WPShadow\Core\Activity_Logger::export_csv( $filters );

		// Send headers
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="wpshadow-activity-' . esc_attr( gmdate( 'Y-m-d-His' ) ) . '.csv"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo $csv; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	// Render activity history view
	include WPSHADOW_PATH . 'includes/views/activity-history.php';
}

// Hook activity rendering into dashboard
add_action( 'wpshadow_dashboard_activity', 'wpshadow_render_recent_activity' );
