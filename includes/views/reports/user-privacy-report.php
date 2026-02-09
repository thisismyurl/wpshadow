<?php
/**
 * User Privacy Report View
 *
 * @package WPShadow
 * @subpackage Reports
 * @since 1.6038.1200
 */

declare(strict_types=1);

use WPShadow\Core\Form_Param_Helper;
use WPShadow\Core\Activity_Logger;
use WPShadow\Privacy\Consent_Preferences;
use WPShadow\Reporting\Report_Snapshot_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user_id = get_current_user_id();
$can_view_others = current_user_can( 'list_users' );
$selected_user_id = $can_view_others
	? (int) Form_Param_Helper::get( 'user_id', 'int', $current_user_id )
	: $current_user_id;

$selected_user = get_user_by( 'id', $selected_user_id );
if ( ! $selected_user ) {
	$selected_user_id = $current_user_id;
	$selected_user    = wp_get_current_user();
}

$user_counts         = count_users();
$total_users         = isset( $user_counts['total_users'] ) ? (int) $user_counts['total_users'] : 0;
$selected_user_label = sprintf( '%1$s (%2$s)', $selected_user->display_name, $selected_user->user_email );
$user_search_nonce   = wp_create_nonce( 'wpshadow_user_search' );
$show_progress       = (bool) Form_Param_Helper::get( 'run_report', 'int', 0 );
$can_manage_reports  = $can_view_others || $selected_user_id === $current_user_id;

$settings     = get_option( 'wpshadow_settings', array() );
$user_meta    = get_user_meta( $selected_user_id );
$wpshadow_meta = array_filter(
	$user_meta,
	function ( $key ) {
		return 0 === strpos( $key, 'wpshadow_' );
	},
	ARRAY_FILTER_USE_KEY
);

$activity_logs      = array();
$activity_log_count = 0;
if ( class_exists( 'WPShadow\\Core\\Activity_Logger' ) ) {
	$result             = Activity_Logger::get_activities( array( 'user_id' => $selected_user_id ), 500, 0 );
	$activity_logs      = isset( $result['activities'] ) ? $result['activities'] : array();
	$activity_log_count = isset( $result['total'] ) ? (int) $result['total'] : count( $activity_logs );
}

$consent = Consent_Preferences::get_preferences( $selected_user_id );

$format_value = function ( $value ) {
	if ( is_bool( $value ) ) {
		return $value ? __( 'Yes', 'wpshadow' ) : __( 'No', 'wpshadow' );
	}
	if ( is_array( $value ) || is_object( $value ) ) {
		return wp_json_encode( $value, JSON_PRETTY_PRINT );
	}
	if ( '' === $value || null === $value ) {
		return __( 'None', 'wpshadow' );
	}
	return (string) $value;
};

$findings = function_exists( 'wpshadow_get_cached_findings' )
	? wpshadow_get_cached_findings()
	: get_option( 'wpshadow_site_findings', array() );
if ( ! is_array( $findings ) ) {
	$findings = array();
}

$report_summary = array(
	'user_label'         => $selected_user_label,
	'settings_count'     => count( $settings ),
	'user_meta_count'    => count( $wpshadow_meta ),
	'activity_log_count' => $activity_log_count,
);

$report_data = array(
	'generated_at'  => current_time( 'mysql' ),
	'summary'       => $report_summary,
	'consent'       => $consent,
	'settings'      => $settings,
	'user_meta'     => $wpshadow_meta,
	'activity_logs' => $activity_logs,
	'findings'      => $findings,
);



require_once WPSHADOW_PATH . 'includes/views/reports/partials/past-reports.php';

if ( $show_progress && $can_manage_reports && class_exists( 'WPShadow\\Reporting\\Report_Snapshot_Manager' ) ) {
	Report_Snapshot_Manager::save_snapshot(
		'user-privacy-report',
		$report_data,
		array(
			'user_id'      => $selected_user_id,
			'user_email'   => $selected_user->user_email,
			'requested_by' => $current_user_id,
			'summary'      => $report_summary,
		)
	);
}

$past_reports = array();
$past_reports_total = 0;
$past_reports_per_page = 10;
$past_reports_pages = 1;
$past_reports_page = 1;
$last_report_time = 0;
if ( class_exists( 'WPShadow\\Reporting\\Report_Snapshot_Manager' ) ) {
	global $wpdb;
	$table_name  = $wpdb->prefix . 'wpshadow_report_snapshots';
	$table_match = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

	if ( $table_match === $table_name ) {
		$past_reports_total = Report_Snapshot_Manager::get_snapshots_for_user_count( 'user-privacy-report', $selected_user_id );
		$past_reports_pages = max( 1, (int) ceil( $past_reports_total / $past_reports_per_page ) );
		$past_reports_page = (int) Form_Param_Helper::get( 'privacy_past_page', 'int', 1 );
		$past_reports_page = max( 1, min( $past_reports_page, $past_reports_pages ) );
		if ( $show_progress ) {
			$past_reports_page = 1;
		}
		$past_reports_offset = ( $past_reports_page - 1 ) * $past_reports_per_page;
		$past_reports = Report_Snapshot_Manager::get_snapshots_for_user( 'user-privacy-report', $selected_user_id, $past_reports_per_page, $past_reports_offset );
		$latest_snapshots = Report_Snapshot_Manager::get_snapshots_for_user( 'user-privacy-report', $selected_user_id, 1, 0 );
		if ( ! empty( $latest_snapshots[0]['created_at'] ) ) {
			$last_report_time = strtotime( $latest_snapshots[0]['created_at'] );
		}
	}

	$past_reports_items = array();
	foreach ( $past_reports as $report ) {
		$past_reports_items[] = array(
			'title'   => __( 'Privacy Report', 'wpshadow' ),
			'time'    => $report['created_at'] ?? 0,
			'actions' => array(
				array(
					'label' => __( 'Download JSON', 'wpshadow' ),
					'url'   => wp_nonce_url(
						add_query_arg(
							array(
								'page'        => 'wpshadow-reports',
								'report'      => 'user-privacy-report',
								'user_id'     => $selected_user_id,
								'download'    => 'json',
								'snapshot_id' => $report['id'],
							),
							admin_url( 'admin.php' )
						),
						'wpshadow_download_user_privacy_report',
						'nonce'
					),
				),
				array(
					'label' => __( 'Download PDF', 'wpshadow' ),
					'url'   => wp_nonce_url(
						add_query_arg(
							array(
								'page'        => 'wpshadow-reports',
								'report'      => 'user-privacy-report',
								'user_id'     => $selected_user_id,
								'download'    => 'pdf',
								'snapshot_id' => $report['id'],
							),
							admin_url( 'admin.php' )
						),
						'wpshadow_download_user_privacy_report',
						'nonce'
					),
				),
			),
		);
	}
}

$last_report_label = $last_report_time
	? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_report_time )
	: __( 'Not run yet', 'wpshadow' );

$history_per_page    = 10;
$history_page        = max( 1, (int) Form_Param_Helper::get( 'history_page', 'int', 1 ) );
$history_page        = $show_progress ? 1 : $history_page;
$history_total       = count( $past_reports );
$history_total_pages = $history_total ? (int) ceil( $history_total / $history_per_page ) : 1;
$history_page        = min( $history_page, $history_total_pages );
$history_offset      = ( $history_page - 1 ) * $history_per_page;
$history_reports     = array_slice( $past_reports, $history_offset, $history_per_page );

$privacy_score = 100;
if ( ! empty( $consent['anonymized_telemetry'] ) ) {
	$privacy_score -= 20;
}
if ( empty( $consent['consented_at'] ) ) {
	$privacy_score -= 10;
}
$privacy_score = max( 0, min( 100, $privacy_score ) );

if ( $privacy_score >= 80 ) {
	$privacy_label = __( 'High', 'wpshadow' );
	$privacy_message = __( 'Your privacy settings look strong for this user.', 'wpshadow' );
	$privacy_color = '#10b981';
} elseif ( $privacy_score >= 60 ) {
	$privacy_label = __( 'Moderate', 'wpshadow' );
	$privacy_message = __( 'There are a few simple ways to strengthen privacy for this user.', 'wpshadow' );
	$privacy_color = '#f59e0b';
} else {
	$privacy_label = __( 'Needs attention', 'wpshadow' );
	$privacy_message = __( 'A few privacy settings could be improved for this user.', 'wpshadow' );
	$privacy_color = '#ef4444';
}

?>

<div class="wrap wps-page-container">
	<?php
	wpshadow_render_page_header(
		__( 'User Privacy Report', 'wpshadow' ),
		__( 'See exactly what WPShadow stores about a specific user, in plain language.', 'wpshadow' ),
		'dashicons-id-alt'
	);
	?>

	<style>
		.wps-user-privacy-columns {
			display: flex;
			gap: var(--wps-space-4);
			align-items: stretch;
			flex-wrap: wrap;
		}

		.wps-user-privacy-main {
			flex: 2 1 520px;
		}

		.wps-user-privacy-side {
			flex: 1 1 320px;
		}

		.wps-user-privacy-table pre {
			white-space: pre-wrap;
			word-break: break-word;
			background: #f9fafb;
			border: 1px solid #e5e7eb;
			border-radius: 8px;
			padding: 12px;
			margin: 0;
			font-size: 12px;
			line-height: 1.5;
		}

		.wps-user-search-results {
			border: 1px solid #e5e7eb;
			border-radius: 8px;
			margin-top: 6px;
			max-height: 220px;
			overflow-y: auto;
			background: #fff;
		}

		.wps-user-search-option {
			display: block;
			width: 100%;
			text-align: left;
			padding: 8px 12px;
			border: none;
			background: transparent;
			cursor: pointer;
		}

		.wps-user-search-option:hover,
		.wps-user-search-option:focus {
			background: #f3f4f6;
		}

		.wps-user-search-empty {
			padding: 8px 12px;
			color: #6b7280;
		}

		.wps-report-progress {
			border: 1px solid #e5e7eb;
			border-radius: 12px;
			padding: 16px;
			background: #f8fafc;
		}

		.wps-report-progress.hidden {
			display: none;
		}

		.wps-report-progress-bar {
			height: 10px;
			background: #e5e7eb;
			border-radius: 999px;
			overflow: hidden;
		}

		.wps-report-progress-fill {
			height: 100%;
			width: 0;
			background: #3b82f6;
			transition: width 0.4s ease;
		}

		.wps-report-progress-list {
			list-style: none;
			padding: 0;
			margin: 12px 0 0;
		}

		.wps-report-progress-list li {
			display: flex;
			gap: 8px;
			align-items: center;
			font-size: 13px;
			color: #4b5563;
			margin-bottom: 6px;
		}

		.wps-report-progress-status {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 18px;
			height: 18px;
			border-radius: 999px;
			font-size: 11px;
			background: #e5e7eb;
			color: #6b7280;
		}

		.wps-report-progress-status.is-running {
			background: #dbeafe;
			color: #1d4ed8;
		}

		.wps-report-progress-status.is-done {
			background: #dcfce7;
			color: #15803d;
		}

		.wps-report-progress-content-hidden {
			display: none;
		}
	</style>

	<div class="wps-user-privacy-columns wps-mb-4">
		<div class="wps-user-privacy-main">
			<div class="wps-card wps-mb-4">
				<div class="wps-card-body">
					<h3 class="wps-text-lg wps-mb-2">
						<?php esc_html_e( 'Report Options', 'wpshadow' ); ?>
					</h3>
					<p class="wps-text-sm wps-text-muted wps-mb-3">
						<?php esc_html_e( 'Choose which user to include in this report.', 'wpshadow' ); ?>
					</p>
					<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" id="user-privacy-report-form">
						<input type="hidden" name="page" value="wpshadow-reports" />
						<input type="hidden" name="report" value="user-privacy-report" />
						<input type="hidden" name="run_report" value="1" />
						<?php wp_nonce_field( 'wpshadow_refresh_privacy_reports', 'wpshadow_refresh_privacy_reports_nonce' ); ?>
						<?php if ( $can_view_others ) : ?>
							<?php if ( $total_users <= 1 ) : ?>
								<p class="wps-text-sm wps-text-muted">
									<?php esc_html_e( 'Only one user is available, so this report shows that user automatically.', 'wpshadow' ); ?>
								</p>
								<input type="hidden" name="user_id" id="user-privacy-report-user-id" value="<?php echo esc_attr( $selected_user_id ); ?>" />
							<?php elseif ( $total_users > 25 ) : ?>
								<label class="wps-form-label" for="user-privacy-report-search">
									<?php esc_html_e( 'Search user', 'wpshadow' ); ?>
								</label>
								<input
									type="text"
									id="user-privacy-report-search"
									class="wps-input"
									value="<?php echo esc_attr( $selected_user_label ); ?>"
									placeholder="<?php esc_attr_e( 'Start typing a name or email', 'wpshadow' ); ?>"
									data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
									data-ajax-nonce="<?php echo esc_attr( $user_search_nonce ); ?>"
									aria-describedby="user-privacy-report-search-help"
									autocomplete="off"
								/>
								<p class="wps-text-xs wps-text-muted wps-mb-2" id="user-privacy-report-search-help">
									<?php esc_html_e( 'Type at least two letters to search.', 'wpshadow' ); ?>
								</p>
								<input type="hidden" name="user_id" id="user-privacy-report-user-id" value="<?php echo esc_attr( $selected_user_id ); ?>" />
								<div class="wps-user-search-results" id="user-privacy-report-results" role="listbox" aria-label="<?php esc_attr_e( 'User search results', 'wpshadow' ); ?>"></div>
							<?php else : ?>
								<label class="wps-form-label" for="user-privacy-report-user">
									<?php esc_html_e( 'Select user', 'wpshadow' ); ?>
								</label>
								<select class="wps-input wps-mb-3" id="user-privacy-report-user" name="user_id">
									<?php foreach ( get_users( array( 'number' => 25, 'orderby' => 'display_name', 'order' => 'ASC' ) ) as $user ) : ?>
										<option value="<?php echo esc_attr( $user->ID ); ?>" <?php selected( $selected_user_id, $user->ID ); ?>>
											<?php echo esc_html( $user->display_name ); ?> (<?php echo esc_html( $user->user_email ); ?>)
										</option>
									<?php endforeach; ?>
								</select>
							<?php endif; ?>
						<?php else : ?>
							<p class="wps-text-sm wps-text-muted">
								<?php esc_html_e( 'You can only view your own report.', 'wpshadow' ); ?>
							</p>
							<input type="hidden" name="user_id" id="user-privacy-report-user-id" value="<?php echo esc_attr( $selected_user_id ); ?>" />
						<?php endif; ?>
						<button class="wps-btn wps-btn--primary" type="submit">
							<span class="dashicons dashicons-update"></span>
							<?php esc_html_e( 'Run Report', 'wpshadow' ); ?>
						</button>
					</form>
					
					<script>
						(function() {
							'use strict';
							
							var form = document.getElementById('user-privacy-report-form');
							if (!form) {
								return;
							}
							
							form.addEventListener('submit', function(e) {
								e.preventDefault();
								
								var userId = document.getElementById('user-privacy-report-user-id') || 
											document.getElementById('user-privacy-report-user');
								var userIdValue = userId ? (userId.value || userId.options[userId.selectedIndex].value) : 0;
								
								if (!userIdValue) {
									alert('<?php echo esc_js( __( 'Please select a user', 'wpshadow' ) ); ?>');
									return;
								}
								
								var nonceField = form.querySelector('input[name="wpshadow_refresh_privacy_reports_nonce"]');
								if (!nonceField) {
									alert('<?php echo esc_js( __( 'Security check failed', 'wpshadow' ) ); ?>');
									return;
								}
								
								// Show progress card
								var progressCard = document.getElementById('user-privacy-report-progress');
								if (!progressCard) {
									// Create progress card
									progressCard = document.createElement('div');
									progressCard.id = 'user-privacy-report-progress';
									progressCard.setAttribute('data-run', '1');
									progressCard.className = 'wps-card wps-mb-4';
									progressCard.innerHTML = `
										<div class="wps-card-body">
											<h3 class="wps-text-lg wps-mb-2">
												<?php esc_html_e( 'Report Progress', 'wpshadow' ); ?>
											</h3>
											<p class="wps-text-sm wps-text-muted wps-mb-3" id="user-privacy-report-progress-message">
												<?php esc_html_e( 'Preparing your privacy report...', 'wpshadow' ); ?>
											</p>
											<div class="wps-report-progress" role="status" aria-live="polite">
												<div class="wps-report-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
													<div class="wps-report-progress-fill" id="user-privacy-report-progress-fill"></div>
												</div>
												<ul class="wps-report-progress-list" id="user-privacy-report-progress-steps">
													<li data-step="identify">
														<span class="wps-report-progress-status">1</span>
														<?php esc_html_e( 'Identifying the selected user', 'wpshadow' ); ?>
													</li>
													<li data-step="settings">
														<span class="wps-report-progress-status">2</span>
														<?php esc_html_e( 'Collecting WPShadow settings', 'wpshadow' ); ?>
													</li>
													<li data-step="meta">
														<span class="wps-report-progress-status">3</span>
														<?php esc_html_e( 'Reviewing user preferences', 'wpshadow' ); ?>
													</li>
													<li data-step="activity">
														<span class="wps-report-progress-status">4</span>
														<?php esc_html_e( 'Gathering recent activity logs', 'wpshadow' ); ?>
													</li>
													<li data-step="snapshot">
														<span class="wps-report-progress-status">5</span>
														<?php esc_html_e( 'Saving a report snapshot', 'wpshadow' ); ?>
													</li>
												</ul>
											</div>
										</div>
									`;
									form.parentNode.insertBefore(progressCard, form.nextSibling);
								}
								
								progressCard.style.display = 'block';
								var progressFill = document.getElementById('user-privacy-report-progress-fill');
								var progressMessage = document.getElementById('user-privacy-report-progress-message');
								var progressSteps = document.getElementById('user-privacy-report-progress-steps');
								var reportContent = document.getElementById('user-privacy-report-content');
								if (!progressFill || !progressMessage || !progressSteps) {
									form.submit();
									return;
								}
								
								if (reportContent) {
									reportContent.classList.add('wps-report-progress-content-hidden');
								}
								
								// Call server to save the report
								jQuery.post('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
									action: 'wpshadow_run_privacy_report',
									user_id: userIdValue,
									nonce: nonceField.value
								}).done(function(response) {
									// Response received, now show progress animation
									runProgressAnimation(progressCard, progressFill, progressMessage, progressSteps, reportContent, userIdValue);
								}).fail(function() {
									// Fallback: do full page reload
									form.submit();
								});
							});
							
							function runProgressAnimation(card, fill, message, steps, content, userId) {
								var stepsList = Array.prototype.slice.call(steps.querySelectorAll('li'));
								var currentIndex = 0;
								var progressValue = 5;
								var stepMessages = [
									'<?php echo esc_js( __( 'Preparing your privacy report...', 'wpshadow' ) ); ?>',
									'<?php echo esc_js( __( 'Collecting report details...', 'wpshadow' ) ); ?>',
									'<?php echo esc_js( __( 'Summarizing privacy data...', 'wpshadow' ) ); ?>',
									'<?php echo esc_js( __( 'Finalizing your report...', 'wpshadow' ) ); ?>'
								];
								
								var updateProgress = function(value) {
									var clamped = Math.max(0, Math.min(100, value));
									fill.style.width = clamped + '%';
									fill.parentNode.setAttribute('aria-valuenow', clamped);
								};
								
								var markStep = function(index, status) {
									if (!stepsList[index]) {
										return;
									}
									var statusEl = stepsList[index].querySelector('.wps-report-progress-status');
									if (!statusEl) {
										return;
									}
									statusEl.classList.remove('is-running', 'is-done');
									if (status === 'running') {
										statusEl.classList.add('is-running');
									} else if (status === 'done') {
										statusEl.classList.add('is-done');
										statusEl.textContent = 'OK';
									}
								};
								
								markStep(0, 'running');
								updateProgress(progressValue);
								message.textContent = stepMessages[0];
								
								var intervalId = window.setInterval(function() {
									markStep(currentIndex, 'done');
									currentIndex = Math.min(stepsList.length - 1, currentIndex + 1);
									markStep(currentIndex, 'running');
									progressValue = Math.min(95, progressValue + 18);
									updateProgress(progressValue);
									message.textContent = stepMessages[Math.min(stepMessages.length - 1, currentIndex)];
									if (currentIndex >= stepsList.length - 1) {
										window.clearInterval(intervalId);
										markStep(currentIndex, 'done');
										updateProgress(100);
										message.textContent = '<?php echo esc_js( __( 'Report ready.', 'wpshadow' ) ); ?>';
										
										// Refresh past reports list after animation
										refreshPastReports(userId);
										
										if (content) {
											content.classList.remove('wps-report-progress-content-hidden');
										}
									}
								}, 700);
							}
							
							function refreshPastReports(userId) {
								var nonceField = form.querySelector('input[name="wpshadow_refresh_privacy_reports_nonce"]');
								if (!nonceField) {
									return;
								}
								
								jQuery.post('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
									action: 'wpshadow_refresh_privacy_reports',
									user_id: userId,
									nonce: nonceField.value
								}).done(function(response) {
									if (response && response.success && response.data && response.data.html) {
										var pastReportsContainer = document.getElementById('user-privacy-past-reports');
										if (pastReportsContainer) {
											pastReportsContainer.innerHTML = response.data.html;
										}
									}
								});
							}
						})();
					</script>
					<?php if ( $can_view_others && $total_users > 25 ) : ?>
						<script>
							(function() {
								var searchInput = document.getElementById('user-privacy-report-search');
								var resultsBox = document.getElementById('user-privacy-report-results');
								var userIdField = document.getElementById('user-privacy-report-user-id');
								var timeoutId = null;

								if (!searchInput || !resultsBox || !userIdField) {
									return;
								}

								var renderResults = function(items) {
									resultsBox.innerHTML = '';

									if (!items.length) {
										resultsBox.innerHTML = '<div class="wps-user-search-empty"><?php echo esc_html( __( 'No matching users found.', 'wpshadow' ) ); ?></div>';
										return;
									}

									items.forEach(function(user) {
										var button = document.createElement('button');
										button.type = 'button';
										button.className = 'wps-user-search-option';
										button.textContent = user.label;
										button.addEventListener('click', function() {
											userIdField.value = user.id;
											searchInput.value = user.label;
											resultsBox.innerHTML = '';
										});
										resultsBox.appendChild(button);
									});
								};

								var runSearch = function(query) {
									resultsBox.innerHTML = '';

									if (query.length < 2) {
										return;
									}

									window.clearTimeout(timeoutId);
									timeoutId = window.setTimeout(function() {
										jQuery.post(searchInput.dataset.ajaxUrl, {
											action: 'wpshadow_user_search',
											term: query,
											nonce: searchInput.dataset.ajaxNonce
										}).done(function(response) {
											if (response && response.success && response.data && response.data.users) {
												renderResults(response.data.users);
											} else {
												renderResults([]);
											}
										}).fail(function() {
											renderResults([]);
										});
									}, 300);
								};

								searchInput.addEventListener('input', function(event) {
									runSearch(event.target.value.trim());
								});
							})();
						</script>
					<?php endif; ?>

				</div>
			</div>

			<?php if ( $show_progress ) : ?>
				<div class="wps-card wps-mb-4" id="user-privacy-report-progress" data-run="1">
					<div class="wps-card-body">
						<h3 class="wps-text-lg wps-mb-2">
							<?php esc_html_e( 'Report Progress', 'wpshadow' ); ?>
						</h3>
						<p class="wps-text-sm wps-text-muted wps-mb-3" id="user-privacy-report-progress-message">
							<?php esc_html_e( 'Preparing your privacy report...', 'wpshadow' ); ?>
						</p>
						<div class="wps-report-progress" role="status" aria-live="polite">
							<div class="wps-report-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
								<div class="wps-report-progress-fill" id="user-privacy-report-progress-fill"></div>
							</div>
							<ul class="wps-report-progress-list" id="user-privacy-report-progress-steps">
								<li data-step="identify">
									<span class="wps-report-progress-status">1</span>
									<?php esc_html_e( 'Identifying the selected user', 'wpshadow' ); ?>
								</li>
								<li data-step="settings">
									<span class="wps-report-progress-status">2</span>
									<?php esc_html_e( 'Collecting WPShadow settings', 'wpshadow' ); ?>
								</li>
								<li data-step="meta">
									<span class="wps-report-progress-status">3</span>
									<?php esc_html_e( 'Reviewing user preferences', 'wpshadow' ); ?>
								</li>
								<li data-step="activity">
									<span class="wps-report-progress-status">4</span>
									<?php esc_html_e( 'Gathering recent activity logs', 'wpshadow' ); ?>
								</li>
								<li data-step="snapshot">
									<span class="wps-report-progress-status">5</span>
									<?php esc_html_e( 'Saving a report snapshot', 'wpshadow' ); ?>
								</li>
							</ul>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $show_progress ) : ?>
				<script>
					(function() {
						var progressCard = document.getElementById('user-privacy-report-progress');
						if (!progressCard) {
							return;
						}
						var progressFill = document.getElementById('user-privacy-report-progress-fill');
						var progressMessage = document.getElementById('user-privacy-report-progress-message');
						var progressSteps = document.getElementById('user-privacy-report-progress-steps');
						var reportContent = document.getElementById('user-privacy-report-content');
						if (!progressFill || !progressMessage || !progressSteps) {
							return;
						}
						if (reportContent) {
							reportContent.classList.add('wps-report-progress-content-hidden');
						}

						var steps = Array.prototype.slice.call(progressSteps.querySelectorAll('li'));
						var currentIndex = 0;
						var progressValue = 5;
						var stepMessages = [
							'<?php echo esc_js( __( 'Preparing your privacy report...', 'wpshadow' ) ); ?>',
							'<?php echo esc_js( __( 'Collecting report details...', 'wpshadow' ) ); ?>',
							'<?php echo esc_js( __( 'Summarizing privacy data...', 'wpshadow' ) ); ?>',
							'<?php echo esc_js( __( 'Finalizing your report...', 'wpshadow' ) ); ?>'
						];

						var updateProgress = function(value) {
							var clamped = Math.max(0, Math.min(100, value));
							progressFill.style.width = clamped + '%';
							progressFill.parentNode.setAttribute('aria-valuenow', clamped);
						};

						var markStep = function(index, status) {
							if (!steps[index]) {
								return;
							}
							var statusEl = steps[index].querySelector('.wps-report-progress-status');
							if (!statusEl) {
								return;
							}
							statusEl.classList.remove('is-running', 'is-done');
							if (status === 'running') {
								statusEl.classList.add('is-running');
							} else if (status === 'done') {
								statusEl.classList.add('is-done');
								statusEl.textContent = 'OK';
							}
						};

						markStep(0, 'running');
						updateProgress(progressValue);
						progressMessage.textContent = stepMessages[0];

						var intervalId = window.setInterval(function() {
							markStep(currentIndex, 'done');
							currentIndex = Math.min(steps.length - 1, currentIndex + 1);
							markStep(currentIndex, 'running');
							progressValue = Math.min(95, progressValue + 18);
							updateProgress(progressValue);
							progressMessage.textContent = stepMessages[Math.min(stepMessages.length - 1, currentIndex)];
							if (currentIndex >= steps.length - 1) {
								window.clearInterval(intervalId);
								markStep(currentIndex, 'done');
								updateProgress(100);
								progressMessage.textContent = '<?php echo esc_js( __( 'Report ready.', 'wpshadow' ) ); ?>';
								if (reportContent) {
									reportContent.classList.remove('wps-report-progress-content-hidden');
								}
							}
						}, 700);

						if (window.history && window.history.replaceState) {
							var url = new URL(window.location.href);
							url.searchParams.delete('run_report');
							window.history.replaceState({}, document.title, url.toString());
						}
					})();
				</script>
			<?php endif; ?>

			<div id="user-privacy-report-content" class="wps-report-progress-content">
				<div class="wps-card">
				<div class="wps-card-body">
					<h2 class="wps-text-xl wps-mb-3">
						<span class="dashicons dashicons-id-alt wps-text-primary"></span>
						<?php esc_html_e( 'Privacy Overview', 'wpshadow' ); ?>
					</h2>
					<p class="wps-text-muted wps-mb-3">
						<?php esc_html_e( 'This report lists the data WPShadow stores about the selected user. You can share this with the user if they have questions about their privacy.', 'wpshadow' ); ?>
					</p>

					<div class="wps-grid wps-grid-cols-2 wps-gap-3 wps-mb-4">
						<div class="wps-card">
							<div class="wps-card-body">
								<div class="wps-flex wps-items-center wps-gap-2">
									<span class="dashicons dashicons-admin-users wps-text-2xl wps-text-success"></span>
									<div>
										<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'User', 'wpshadow' ); ?></div>
										<div class="wps-text-lg wps-font-semibold"><?php echo esc_html( $selected_user->display_name ); ?></div>
									</div>
								</div>
							</div>
						</div>

						<div class="wps-card">
							<div class="wps-card-body">
								<div class="wps-flex wps-items-center wps-gap-2">
									<span class="dashicons dashicons-admin-settings wps-text-2xl wps-text-primary"></span>
									<div>
										<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Settings', 'wpshadow' ); ?></div>
										<div class="wps-text-lg wps-font-semibold"><?php echo esc_html( count( $settings ) ); ?></div>
									</div>
								</div>
							</div>
						</div>

						<div class="wps-card">
							<div class="wps-card-body">
								<div class="wps-flex wps-items-center wps-gap-2">
									<span class="dashicons dashicons-admin-users wps-text-2xl wps-text-warning"></span>
									<div>
										<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'User Meta', 'wpshadow' ); ?></div>
										<div class="wps-text-lg wps-font-semibold"><?php echo esc_html( count( $wpshadow_meta ) ); ?></div>
									</div>
								</div>
							</div>
						</div>

						<div class="wps-card">
							<div class="wps-card-body">
								<div class="wps-flex wps-items-center wps-gap-2">
									<span class="dashicons dashicons-clock wps-text-2xl wps-text-success"></span>
									<div>
										<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Activity Logs', 'wpshadow' ); ?></div>
										<div class="wps-text-lg wps-font-semibold"><?php echo esc_html( count( $activity_logs ) ); ?></div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<p class="wps-text-sm wps-text-muted">
						<?php esc_html_e( 'Consent: ', 'wpshadow' ); ?>
						<strong>
							<?php echo esc_html( $consent['anonymized_telemetry'] ? __( 'Anonymous usage data enabled', 'wpshadow' ) : __( 'Anonymous usage data disabled', 'wpshadow' ) ); ?>
						</strong>
					</p>
				</div>
			</div>

			<div class="wps-card wps-mt-4">
				<div class="wps-card-body wps-user-privacy-table">
					<h3 class="wps-text-lg wps-mb-2">
						<?php esc_html_e( 'Consent Preferences', 'wpshadow' ); ?>
					</h3>
					<table class="wps-table wps-table-collapse">
						<tr>
							<td class="wps-th-p-2-bold"><?php esc_html_e( 'Anonymous usage data', 'wpshadow' ); ?></td>
							<td class="wps-td-p-2">
								<?php echo esc_html( $consent['anonymized_telemetry'] ? __( 'Enabled', 'wpshadow' ) : __( 'Disabled', 'wpshadow' ) ); ?>
							</td>
						</tr>
						<tr>
							<td class="wps-th-p-2-bold"><?php esc_html_e( 'Last updated', 'wpshadow' ); ?></td>
							<td class="wps-td-p-2">
								<?php echo esc_html( $consent['consented_at'] ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $consent['consented_at'] ) ) : __( 'Not set', 'wpshadow' ) ); ?>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<div class="wps-card wps-mt-4">
				<div class="wps-card-body wps-user-privacy-table">
					<h3 class="wps-text-lg wps-mb-2">
						<?php esc_html_e( 'WPShadow Settings (Site-Wide)', 'wpshadow' ); ?>
					</h3>
					<?php if ( ! empty( $settings ) ) : ?>
						<table class="wps-table wps-table-collapse">
							<?php foreach ( $settings as $setting_key => $setting_value ) : ?>
								<tr>
									<td class="wps-th-p-2-bold"><?php echo esc_html( $setting_key ); ?></td>
									<td class="wps-td-p-2"><pre><?php echo esc_html( $format_value( $setting_value ) ); ?></pre></td>
								</tr>
							<?php endforeach; ?>
						</table>
					<?php else : ?>
						<p class="wps-text-sm wps-text-muted">
							<?php esc_html_e( 'No WPShadow settings saved yet.', 'wpshadow' ); ?>
						</p>
					<?php endif; ?>
				</div>
			</div>

			<div class="wps-card wps-mt-4">
				<div class="wps-card-body wps-user-privacy-table">
					<h3 class="wps-text-lg wps-mb-2">
						<?php esc_html_e( 'WPShadow User Meta', 'wpshadow' ); ?>
					</h3>
					<?php if ( ! empty( $wpshadow_meta ) ) : ?>
						<table class="wps-table wps-table-collapse">
							<?php foreach ( $wpshadow_meta as $meta_key => $meta_value ) : ?>
								<tr>
									<td class="wps-th-p-2-bold"><?php echo esc_html( $meta_key ); ?></td>
									<td class="wps-td-p-2"><pre><?php echo esc_html( $format_value( $meta_value[0] ?? '' ) ); ?></pre></td>
								</tr>
							<?php endforeach; ?>
						</table>
					<?php else : ?>
						<p class="wps-text-sm wps-text-muted">
							<?php esc_html_e( 'No WPShadow user preferences saved yet.', 'wpshadow' ); ?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>

		</div>

		<div class="wps-user-privacy-side">
			<div class="wps-card">
				<div class="wps-card-body">
					<h3 class="wps-text-lg wps-mb-2">
						<?php esc_html_e( 'Privacy Report', 'wpshadow' ); ?>
					</h3>
					<p class="wps-text-sm wps-text-muted wps-mb-3">
						<?php esc_html_e( 'This gauge summarizes privacy settings for the selected user.', 'wpshadow' ); ?>
					</p>
					<svg width="200" height="200" viewBox="0 0 200 200" class="wps-health-gauge-svg" aria-labelledby="privacy-score-title" role="img">
						<title id="privacy-score-title">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %d: privacy score percentage */
									__( 'Privacy score: %d%%', 'wpshadow' ),
									$privacy_score
								)
							);
							?>
						</title>
						<!-- Outer decorative circle -->
						<circle cx="100" cy="100" r="95" fill="none" stroke="<?php echo esc_attr( $privacy_color ); ?>" stroke-width="2" opacity="0.2" />
						<!-- Gauge background -->
						<circle cx="100" cy="100" r="85" fill="none" stroke="#e0e0e0" stroke-width="16" />
						<!-- Gauge progress -->
						<circle cx="100" cy="100" r="85" fill="none" stroke="<?php echo esc_attr( $privacy_color ); ?>" stroke-width="16"
							stroke-dasharray="<?php echo (int) ( $privacy_score / 100 * 534 ); ?> 534"
							stroke-linecap="round" transform="rotate(-90 100 100)"
							class="wps-gauge-progress" />
						<!-- Center text -->
						<text x="100" y="110" text-anchor="middle" font-size="48" font-weight="bold" fill="<?php echo esc_attr( $privacy_color ); ?>"><?php echo esc_html( (int) $privacy_score ); ?>%</text>
						<text x="100" y="135" text-anchor="middle" font-size="16" fill="#666"><?php echo esc_html( $privacy_label ); ?></text>
					</svg>
					<p class="wps-text-xs wps-text-muted wps-mt-2">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: date/time of last report run */
								__( 'Last run: %s', 'wpshadow' ),
								$last_report_label
							)
						);
						?>
					</p>
					<p class="wps-health-gauge-message wps-mt-4"><?php echo esc_html( $privacy_message ); ?></p>
				</div>
			</div>

			<div class="wps-card">
				<div class="wps-card-body">
					<h3 class="wps-text-lg wps-mb-3">
						<?php esc_html_e( 'What This Analysis Covers', 'wpshadow' ); ?>
					</h3>
					<div class="wps-grid wps-grid-cols-1 wps-gap-4">
						<div>
							<h4 class="wps-font-semibold wps-mb-2">
								<span class="dashicons dashicons-admin-users"></span>
								<?php esc_html_e( 'User Account', 'wpshadow' ); ?>
							</h4>
							<ul class="wps-list-disc wps-ml-5 wps-text-sm">
								<li><?php esc_html_e( 'User profile information (name, email)', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Account creation date', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'User roles and capabilities', 'wpshadow' ); ?></li>
							</ul>
						</div>
						<div>
							<h4 class="wps-font-semibold wps-mb-2">
								<span class="dashicons dashicons-admin-settings"></span>
								<?php esc_html_e( 'Data & Preferences', 'wpshadow' ); ?>
							</h4>
							<ul class="wps-list-disc wps-ml-5 wps-text-sm">
								<li><?php esc_html_e( 'WPShadow user preferences', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Consent settings', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'User metadata stored by the system', 'wpshadow' ); ?></li>
							</ul>
						</div>
						<div>
							<h4 class="wps-font-semibold wps-mb-2">
								<span class="dashicons dashicons-clock"></span>
								<?php esc_html_e( 'Activity & History', 'wpshadow' ); ?>
							</h4>
							<ul class="wps-list-disc wps-ml-5 wps-text-sm">
								<li><?php esc_html_e( 'Recent activities and actions', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Report generation history', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Export formats (JSON, PDF)', 'wpshadow' ); ?></li>
							</ul>
						</div>
					</div>
				</div>
			</div>

			<div id="user-privacy-past-reports">
				<?php
				if ( function_exists( 'wpshadow_render_past_reports_card' ) ) {
					wpshadow_render_past_reports_card(
						array(
							'title'         => __( 'Past Reports', 'wpshadow' ),
							'description'   => __( 'Download previous privacy exports for reference or sharing.', 'wpshadow' ),
							'empty_message' => __( 'No past privacy reports yet.', 'wpshadow' ),
							'items'         => $past_reports_items,
							'pagination'    => array(
								'current'  => $past_reports_page,
								'total'    => $past_reports_pages,
								'param'    => 'privacy_past_page',
								'base_url' => add_query_arg(
									array(
										'page'   => 'wpshadow-reports',
										'report' => 'user-privacy-report',
										'user_id' => $selected_user_id,
									),
									admin_url( 'admin.php' )
								),
							),
							'delete_action' => $can_manage_reports && ! empty( $past_reports_items )
								? array(
									'action_url'   => admin_url( 'admin-post.php' ),
									'nonce_action' => 'wpshadow_delete_privacy_reports',
									'nonce_name'   => 'wpshadow_delete_privacy_reports_nonce',
									'fields'       => array(
										'action'  => 'wpshadow_delete_privacy_reports',
										'user_id' => $selected_user_id,
									),
									'label'        => __( 'Delete All Reports', 'wpshadow' ),
									'confirm'      => __( 'Delete all past privacy reports? This cannot be undone.', 'wpshadow' ),
								)
								: array(),
						)
					);
				}
				?>
			</div>
		</div>
	</div>

	<div class="wps-card">
		<div class="wps-card-body wps-user-privacy-table">
			<h3 class="wps-text-lg wps-mb-2">
				<?php esc_html_e( 'Privacy Report History', 'wpshadow' ); ?>
			</h3>
			<p class="wps-text-sm wps-text-muted wps-mb-3">
				<?php esc_html_e( 'This is a history of user privacy reports for the selected user.', 'wpshadow' ); ?>
			</p>
			<?php if ( ! empty( $past_reports ) ) : ?>
				<table class="wps-table wps-table-collapse">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Date', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Summary', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Details', 'wpshadow' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $history_reports as $report ) : ?>
							<?php
								$metadata = isset( $report['metadata'] ) ? $report['metadata'] : array();
								$summary  = isset( $metadata['summary'] ) ? $metadata['summary'] : array();
								$requester_id   = isset( $metadata['requested_by'] ) ? (int) $metadata['requested_by'] : 0;
								$requester      = $requester_id ? get_user_by( 'id', $requester_id ) : null;
								$requester_name = $requester ? $requester->display_name : '';
								$report_user_id = isset( $metadata['user_id'] ) ? (int) $metadata['user_id'] : 0;
								$report_user    = $report_user_id ? get_user_by( 'id', $report_user_id ) : null;
								$report_user_name = $report_user ? $report_user->display_name : $selected_user->display_name;
								$details_text   = $requester_name
									? sprintf(
										/* translators: %s: user name */
										__( 'Requested by %s.', 'wpshadow' ),
										$requester_name
									)
									: __( 'Requested by WPShadow.', 'wpshadow' );
								$summary_text = sprintf(
									/* translators: %s: user name */
									__( 'Privacy Report for %s', 'wpshadow' ),
									$report_user_name
								);
								$created_timestamp = ! empty( $report['created_at'] ) ? strtotime( $report['created_at'] ) : 0;
								$time_ago_label    = '';
								if ( $created_timestamp ) {
									$time_diff = current_time( 'timestamp' ) - $created_timestamp;
									if ( $time_diff < DAY_IN_SECONDS ) {
										$time_ago_label = sprintf(
											/* translators: %s: human readable time difference */
											__( '%s ago', 'wpshadow' ),
											human_time_diff( $created_timestamp, current_time( 'timestamp' ) )
										);
									}
								}
							?>
							<tr>
								<td>
									<?php if ( $created_timestamp ) : ?>
										<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $created_timestamp ) ); ?>
									<?php else : ?>
										<?php esc_html_e( 'Unknown date', 'wpshadow' ); ?>
									<?php endif; ?>
									<?php if ( $time_ago_label ) : ?>
										<br />
										<span class="wps-text-xs wps-text-muted">
											<?php echo esc_html( $time_ago_label ); ?>
										</span>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( $summary_text ); ?></td>
								<td>
									<?php if ( $selected_user_id === $current_user_id ) : ?>
										<?php echo esc_html( $details_text ); ?>
									<?php else : ?>
										<?php esc_html_e( 'Details available to the user.', 'wpshadow' ); ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<?php if ( $history_total_pages > 1 ) : ?>
					<div class="wps-mt-3 wps-flex wps-items-center wps-justify-between">
						<div class="wps-text-sm wps-text-muted">
							<?php
							echo esc_html(
								sprintf(
									/* translators: 1: current page, 2: total pages */
									__( 'Page %1$d of %2$d', 'wpshadow' ),
									$history_page,
									$history_total_pages
								)
							);
							?>
						</div>
						<div class="wps-flex wps-gap-2 wps-items-center">
							<?php if ( $history_page > 1 ) : ?>
								<a class="wps-btn wps-btn--secondary wps-btn--sm" href="<?php echo esc_url( add_query_arg( array( 'history_page' => $history_page - 1 ) ) ); ?>">
									<?php esc_html_e( 'Previous', 'wpshadow' ); ?>
								</a>
							<?php endif; ?>
							<?php
							$history_window = 2;
							$history_start  = max( 1, $history_page - $history_window );
							$history_end    = min( $history_total_pages, $history_page + $history_window );
							if ( $history_start > 1 ) :
								?>
								<a class="wps-btn wps-btn--secondary wps-btn--sm" href="<?php echo esc_url( add_query_arg( array( 'history_page' => 1 ) ) ); ?>">1</a>
								<?php if ( $history_start > 2 ) : ?>
									<span class="wps-text-sm wps-text-muted">...</span>
								<?php endif; ?>
							<?php endif; ?>
							<?php for ( $page = $history_start; $page <= $history_end; $page++ ) : ?>
								<?php if ( $page === $history_page ) : ?>
									<span class="wps-btn wps-btn--primary wps-btn--sm" aria-current="page"><?php echo esc_html( (string) $page ); ?></span>
								<?php else : ?>
									<a class="wps-btn wps-btn--secondary wps-btn--sm" href="<?php echo esc_url( add_query_arg( array( 'history_page' => $page ) ) ); ?>">
										<?php echo esc_html( (string) $page ); ?>
									</a>
								<?php endif; ?>
							<?php endfor; ?>
							<?php if ( $history_end < $history_total_pages ) : ?>
								<?php if ( $history_end < ( $history_total_pages - 1 ) ) : ?>
									<span class="wps-text-sm wps-text-muted">...</span>
								<?php endif; ?>
								<a class="wps-btn wps-btn--secondary wps-btn--sm" href="<?php echo esc_url( add_query_arg( array( 'history_page' => $history_total_pages ) ) ); ?>">
									<?php echo esc_html( (string) $history_total_pages ); ?>
								</a>
							<?php endif; ?>
							<?php if ( $history_page < $history_total_pages ) : ?>
								<a class="wps-btn wps-btn--secondary wps-btn--sm" href="<?php echo esc_url( add_query_arg( array( 'history_page' => $history_page + 1 ) ) ); ?>">
									<?php esc_html_e( 'Next', 'wpshadow' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<p class="wps-text-sm wps-text-muted">
					<?php esc_html_e( 'No privacy report history yet.', 'wpshadow' ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>
</div>
