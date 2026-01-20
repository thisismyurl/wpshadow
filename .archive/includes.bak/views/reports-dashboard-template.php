<?php
/**
 * Reports Dashboard Template
 * 
 * @package WPShadow
 * @var array $issues Current issues
 * @var array $breakdown Severity breakdown
 * @var array $stats Snapshot statistics
 * @var array $latest_snapshot Latest snapshot
 * @var array $history 7-day history
 * @var array $sorted_issues Filtered and sorted issues
 * @var string $severity_filter Active severity filter
 * @var string $search_query Active search query
 * @var string $sort_by Current sort method
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap wpshadow-reports">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<!-- Dashboard Summary -->
	<div class="wpshadow-summary">
		<div class="summary-card critical">
			<div class="summary-icon">⚠️</div>
			<div class="summary-content">
				<div class="summary-label">Critical</div>
				<div class="summary-value"><?php echo esc_html( $breakdown['critical'] ?? 0 ); ?></div>
			</div>
		</div>

		<div class="summary-card high">
			<div class="summary-icon">🔴</div>
			<div class="summary-content">
				<div class="summary-label">High</div>
				<div class="summary-value"><?php echo esc_html( $breakdown['high'] ?? 0 ); ?></div>
			</div>
		</div>

		<div class="summary-card medium">
			<div class="summary-icon">🟡</div>
			<div class="summary-content">
				<div class="summary-label">Medium</div>
				<div class="summary-value"><?php echo esc_html( $breakdown['medium'] ?? 0 ); ?></div>
			</div>
		</div>

		<div class="summary-card low">
			<div class="summary-icon">🟢</div>
			<div class="summary-content">
				<div class="summary-label">Low</div>
				<div class="summary-value"><?php echo esc_html( $breakdown['low'] ?? 0 ); ?></div>
			</div>
		</div>
	</div>

	<!-- Statistics -->
	<div class="wpshadow-stats">
		<div class="stat-box">
			<label><?php esc_html_e( 'Total Issues', 'wpshadow' ); ?></label>
			<span class="stat-value"><?php echo esc_html( count( $issues ) ); ?></span>
		</div>
		<div class="stat-box">
			<label><?php esc_html_e( 'Average (7 days)', 'wpshadow' ); ?></label>
			<span class="stat-value"><?php echo esc_html( $stats['average_issues'] ?? 'N/A' ); ?></span>
		</div>
		<div class="stat-box">
			<label><?php esc_html_e( 'Trend', 'wpshadow' ); ?></label>
			<span class="stat-value trend-<?php echo esc_attr( $stats['trend'] ?? 'stable' ); ?>">
				<?php echo esc_html( ucfirst( $stats['trend'] ?? 'stable' ) ); ?>
			</span>
		</div>
		<div class="stat-box">
			<label><?php esc_html_e( 'Last Scan', 'wpshadow' ); ?></label>
			<span class="stat-value">
				<?php 
					if ( $latest_snapshot ) {
						echo esc_html( wp_date( 'Y-m-d H:i', $latest_snapshot['timestamp'] ) );
					} else {
						esc_html_e( 'Never', 'wpshadow' );
					}
				?>
			</span>
		</div>
	</div>

	<!-- Controls -->
	<div class="wpshadow-controls">
		<button type="button" class="button button-primary" id="btn-refresh-issues">
			<?php esc_html_e( 'Refresh Now', 'wpshadow' ); ?>
		</button>
		<button type="button" class="button" id="btn-export-pdf">
			<?php esc_html_e( 'Export PDF', 'wpshadow' ); ?>
		</button>
	</div>

	<!-- Filters -->
	<div class="wpshadow-filters">
		<form method="get" id="issues-filter-form">
			<input type="hidden" name="page" value="wpshadow-reports">

			<select name="severity" id="filter-severity" class="filter-select">
				<option value=""><?php esc_html_e( 'All Severities', 'wpshadow' ); ?></option>
				<option value="critical" <?php selected( $severity_filter, 'critical' ); ?>>
					<?php esc_html_e( 'Critical', 'wpshadow' ); ?>
				</option>
				<option value="high" <?php selected( $severity_filter, 'high' ); ?>>
					<?php esc_html_e( 'High', 'wpshadow' ); ?>
				</option>
				<option value="medium" <?php selected( $severity_filter, 'medium' ); ?>>
					<?php esc_html_e( 'Medium', 'wpshadow' ); ?>
				</option>
				<option value="low" <?php selected( $severity_filter, 'low' ); ?>>
					<?php esc_html_e( 'Low', 'wpshadow' ); ?>
				</option>
			</select>

			<select name="sort" id="filter-sort" class="filter-select">
				<option value="severity" <?php selected( $sort_by, 'severity' ); ?>>
					<?php esc_html_e( 'Sort by Severity', 'wpshadow' ); ?>
				</option>
				<option value="name" <?php selected( $sort_by, 'name' ); ?>>
					<?php esc_html_e( 'Sort by Name', 'wpshadow' ); ?>
				</option>
				<option value="time" <?php selected( $sort_by, 'time' ); ?>>
					<?php esc_html_e( 'Sort by Time', 'wpshadow' ); ?>
				</option>
			</select>

			<input type="text" name="search" class="filter-search" placeholder="<?php esc_attr_e( 'Search issues...', 'wpshadow' ); ?>" value="<?php echo esc_attr( $search_query ); ?>">

			<button type="submit" class="button">
				<?php esc_html_e( 'Filter', 'wpshadow' ); ?>
			</button>
		</form>
	</div>

	<!-- Issues Table -->
	<div class="wpshadow-issues">
		<?php if ( empty( $sorted_issues ) ) : ?>
			<div class="notice notice-success">
				<p><?php esc_html_e( 'No issues detected! Your site is healthy.', 'wpshadow' ); ?></p>
			</div>
		<?php else : ?>
			<table class="wp-list-table widefat striped" role="grid">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Severity', 'wpshadow' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Title', 'wpshadow' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Description', 'wpshadow' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Detected', 'wpshadow' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Actions', 'wpshadow' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $sorted_issues as $issue_id => $issue ) : ?>
						<tr class="issue-row issue-<?php echo esc_attr( $issue['severity'] ?? 'unknown' ); ?>">
							<td class="severity-cell">
								<span class="severity-badge severity-<?php echo esc_attr( $issue['severity'] ?? 'unknown' ); ?>">
									<?php echo esc_html( ucfirst( $issue['severity'] ?? 'Unknown' ) ); ?>
								</span>
							</td>
							<td class="title-cell">
								<strong><?php echo esc_html( $issue['title'] ?? 'Untitled' ); ?></strong>
							</td>
							<td class="description-cell">
								<?php echo esc_html( wp_trim_words( $issue['description'] ?? '', 15 ) ); ?>
							</td>
							<td class="time-cell">
								<?php 
									if ( isset( $issue['detected_at'] ) ) {
										echo esc_html( wp_date( 'M d, Y H:i', $issue['detected_at'] ) );
									} else {
										esc_html_e( 'N/A', 'wpshadow' );
									}
								?>
							</td>
							<td class="actions-cell">
								<button type="button" class="btn-dismiss" data-issue-id="<?php echo esc_attr( $issue_id ); ?>" aria-label="<?php esc_attr_e( 'Dismiss issue', 'wpshadow' ); ?>">
									<?php esc_html_e( 'Dismiss', 'wpshadow' ); ?>
								</button>
								<button type="button" class="btn-details" aria-label="<?php esc_attr_e( 'View details', 'wpshadow' ); ?>">
									<?php esc_html_e( 'Details', 'wpshadow' ); ?>
								</button>
							</td>
						</tr>
						<tr class="details-row" style="display: none;">
							<td colspan="5">
								<div class="issue-details">
									<div class="detail-section">
										<label><?php esc_html_e( 'Issue ID:', 'wpshadow' ); ?></label>
										<span><?php echo esc_html( $issue_id ); ?></span>
									</div>
									<div class="detail-section">
										<label><?php esc_html_e( 'Detector:', 'wpshadow' ); ?></label>
										<span><?php echo esc_html( $issue['detector_id'] ?? 'N/A' ); ?></span>
									</div>
									<div class="detail-section">
										<label><?php esc_html_e( 'Confidence:', 'wpshadow' ); ?></label>
										<span><?php echo esc_html( round( ( $issue['confidence'] ?? 0 ) * 100 ) ) . '%'; ?></span>
									</div>
									<div class="detail-section full-width">
										<label><?php esc_html_e( 'Description:', 'wpshadow' ); ?></label>
										<p><?php echo esc_html( $issue['description'] ?? '' ); ?></p>
									</div>
									<div class="detail-section full-width">
										<label><?php esc_html_e( 'Resolution:', 'wpshadow' ); ?></label>
										<p><?php echo esc_html( $issue['resolution'] ?? '' ); ?></p>
									</div>
									<?php if ( $issue['auto_fixable'] ?? false ) : ?>
										<div class="detail-section">
											<span class="auto-fixable-badge"><?php esc_html_e( '✓ Can be auto-fixed', 'wpshadow' ); ?></span>
										</div>
									<?php endif; ?>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<!-- History Chart (Placeholder) -->
	<div class="wpshadow-history">
		<h2><?php esc_html_e( '7-Day History', 'wpshadow' ); ?></h2>
		<div class="history-chart">
			<?php foreach ( array_reverse( $history ) as $date => $snapshot ) : ?>
				<div class="history-bar">
					<div class="bar-value" style="height: <?php echo esc_attr( min( ( $snapshot['total_issues'] ?? 0 ) * 10, 100 ) ); ?>%;" 
						 title="<?php echo esc_attr( wp_date( 'Y-m-d', strtotime( $date ) ) ); ?> - <?php echo esc_attr( $snapshot['total_issues'] ?? 0 ); ?> issues">
					</div>
					<div class="bar-label"><?php echo esc_html( wp_date( 'M d', strtotime( $date ) ) ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
