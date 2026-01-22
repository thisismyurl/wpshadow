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

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get recent activity for dashboard
 * 
 * @return array Recent activity entries with action and timestamp
 */
function wpshadow_get_recent_activity() : array {
    $log = get_option( 'wpshadow_finding_log', array() );
    $activity = array();

    // Convert finding log to activity format
    foreach ( array_reverse( array_slice( $log, -10 ) ) as $entry ) {
        $action_label = '';

        switch ( $entry['action'] ) {
            case 'auto_fixed':
                $action_label = '🔧 ' . __( 'Auto-fixed', 'wpshadow' ) . ': ' . ucwords( str_replace( '-', ' ', $entry['finding_id'] ) );
                break;
            case 'dismissed':
                $action_label = '👁️ ' . __( 'Dismissed', 'wpshadow' ) . ': ' . ucwords( str_replace( '-', ' ', $entry['finding_id'] ) );
                break;
            case 'scheduled':
                $action_label = '📅 ' . __( 'Scheduled deep scans', 'wpshadow' );
                break;
            default:
                $action_label = ucwords( str_replace( '-', ' ', $entry['finding_id'] ) );
        }

        if ( ! empty( $entry['message'] ) ) {
            $action_label .= ' - ' . $entry['message'];
        }

        $activity[] = array(
            'action'    => $action_label,
            'time'      => $entry['timestamp'],
            'category'  => $entry['category'] ?? '',
        );
    }

    // Add activation as fallback if no log entries
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
function wpshadow_format_time_with_tooltip( int $timestamp ) : string {
    $now = current_time( 'timestamp' );
    $diff = $now - $timestamp;

    if ( $diff < 60 ) {
        $relative = __( 'just now', 'wpshadow' );
    } elseif ( $diff < 3600 ) {
        $minutes = (int) floor( $diff / 60 );
        $relative = sprintf( _n( '%d minute ago', '%d minutes ago', $minutes, 'wpshadow' ), $minutes );
    } elseif ( $diff < 86400 ) {
        $hours = (int) floor( $diff / 3600 );
        $relative = sprintf( _n( '%d hour ago', '%d hours ago', $hours, 'wpshadow' ), $hours );
    } else {
        $days = (int) floor( $diff / 86400 );
        $relative = sprintf( _n( '%d day ago', '%d days ago', $days, 'wpshadow' ), $days );
    }

    $precise = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );

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
function wpshadow_render_recent_activity() : void {
	$activity = wpshadow_get_recent_activity();
	
	if ( empty( $activity ) ) {
		return; // No activity to display
	}
	?>
	<div style="margin: 30px 0;">
		<h2><?php esc_html_e( 'Recent Activity', 'wpshadow' ); ?></h2>
		<table class="wp-list-table widefat">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Action', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Time', 'wpshadow' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $activity as $entry ) : ?>
				<tr>
					<td><?php echo esc_html( $entry['action'] ); ?></td>
					<td><?php echo wp_kses_post( wpshadow_format_time_with_tooltip( $entry['time'] ) ); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
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
function wpshadow_render_activity_page() : void {
    if ( ! current_user_can( 'read' ) ) {
        wp_die( __( 'Insufficient permissions.', 'wpshadow' ) );
    }

    // Handle CSV export
    if ( isset( $_GET['export'] ) && 'csv' === $_GET['export'] ) {
        // Build filters for export
        $filters = array();
        if ( ! empty( $_GET['activity_category'] ) ) {
            $filters['category'] = sanitize_key( wp_unslash( $_GET['activity_category'] ) );
        }
        if ( ! empty( $_GET['activity_action'] ) ) {
            $filters['action'] = sanitize_key( wp_unslash( $_GET['activity_action'] ) );
        }
        if ( ! empty( $_GET['activity_search'] ) ) {
            $filters['search'] = sanitize_text_field( wp_unslash( $_GET['activity_search'] ) );
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
