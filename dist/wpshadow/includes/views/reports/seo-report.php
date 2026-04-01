<?php
/**
 * SEO Report View
 *
 * Comprehensive SEO analysis and report generation for the entire site.
 *
 * @package WPShadow
 * @subpackage Reports
 * @since 0.6093.1200
 */

declare(strict_types=1);

use WPShadow\Core\Form_Param_Helper;
use WPShadow\Diagnostics\Diagnostic_Registry;
use WPShadow\Reporting\Report_Snapshot_Manager;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

$current_user_id  = get_current_user_id();
$show_progress    = (bool) Form_Param_Helper::get( 'run_report', 'int', 0 );
$seo_report_nonce = wp_create_nonce( 'wpshadow_refresh_seo_reports' );

// Collect SEO data
$seo_diagnostics = array();
$all_diagnostics = Diagnostic_Registry::get_all();

foreach ( $all_diagnostics as $slug => $class ) {
if ( ! class_exists( $class ) ) {
continue;
}

$family = method_exists( $class, 'get_family' ) ? $class::get_family() : '';
if ( 'seo' === $family ) {
$seo_diagnostics[ $slug ] = $class;
}
}

// Get SEO findings
$findings = function_exists( 'wpshadow_get_cached_findings' )
? wpshadow_get_cached_findings()
: get_option( 'wpshadow_site_findings', array() );
if ( ! is_array( $findings ) ) {
$findings = array();
}

// Filter to SEO findings only
$seo_findings = array_filter(
$findings,
function ( $finding ) {
return isset( $finding['family'] ) && 'seo' === $finding['family'];
}
);

// Get site SEO metadata
$site_title        = get_bloginfo( 'name' );
$site_description  = get_bloginfo( 'description' );
$site_url          = get_site_url();
$robots_txt_exists = file_exists( ABSPATH . 'robots.txt' );
$sitemap_url       = get_sitemap_url( 'index' );

// Check for SEO plugins
$seo_plugins    = array();
$active_plugins = get_option( 'active_plugins', array() );
foreach ( $active_plugins as $plugin ) {
if ( false !== strpos( $plugin, 'seo' ) || false !== strpos( $plugin, 'yoast' ) || false !== strpos( $plugin, 'rank-math' ) ) {
$seo_plugins[] = $plugin;
}
}

$report_summary = array(
'site_url'          => $site_url,
'site_title'        => $site_title,
'seo_issues_count'  => count( $seo_findings ),
'diagnostics_count' => count( $seo_diagnostics ),
'has_robots_txt'    => $robots_txt_exists,
'has_sitemap'       => ! empty( $sitemap_url ),
'seo_plugins_count' => count( $seo_plugins ),
);

$report_data = array(
'generated_at' => current_time( 'mysql' ),
'summary'      => $report_summary,
'findings'     => $seo_findings,
'diagnostics'  => array_keys( $seo_diagnostics ),
'seo_metadata' => array(
'title'       => $site_title,
'description' => $site_description,
'url'         => $site_url,
'robots_txt'  => $robots_txt_exists,
'sitemap'     => $sitemap_url,
),
'seo_plugins'  => $seo_plugins,
);

require_once WPSHADOW_PATH . 'includes/views/reports/partials/past-reports.php';

if ( $show_progress && current_user_can( 'manage_options' ) && class_exists( 'WPShadow\\Reporting\\Report_Snapshot_Manager' ) ) {
Report_Snapshot_Manager::save_snapshot(
'seo-report',
$report_data,
array(
'requested_by' => $current_user_id,
'summary'      => $report_summary,
)
);
}

$past_reports          = array();
$past_reports_total    = 0;
$past_reports_per_page = 10;
$past_reports_pages    = 1;
$past_reports_page     = 1;
$past_reports_items    = array();
$last_report_time      = 0;
if ( class_exists( 'WPShadow\\Reporting\\Report_Snapshot_Manager' ) ) {
if ( Report_Snapshot_Manager::has_snapshots_table() ) {
$past_reports_total = Report_Snapshot_Manager::get_snapshots_count( 'seo-report' );
$past_reports_pages = max( 1, (int) ceil( $past_reports_total / $past_reports_per_page ) );
$past_reports_page  = (int) Form_Param_Helper::get( 'seo_past_page', 'int', 1 );
$past_reports_page  = max( 1, min( $past_reports_page, $past_reports_pages ) );
if ( $show_progress ) {
$past_reports_page = 1;
}
$past_reports_offset = ( $past_reports_page - 1 ) * $past_reports_per_page;
$past_reports        = Report_Snapshot_Manager::get_snapshots_paginated( 'seo-report', $past_reports_per_page, $past_reports_offset );
$latest_snapshots    = Report_Snapshot_Manager::get_snapshots( 'seo-report', 1 );
if ( ! empty( $latest_snapshots[0]['created_at'] ) ) {
$last_report_time = strtotime( $latest_snapshots[0]['created_at'] );
}
}

foreach ( $past_reports as $report ) {
$past_reports_items[] = array(
'title'   => __( 'SEO Report', 'wpshadow' ),
'time'    => $report['created_at'] ?? 0,
'actions' => array(
array(
'label' => __( 'Download JSON', 'wpshadow' ),
'url'   => wp_nonce_url(
add_query_arg(
array(
'page'        => 'wpshadow-reports',
'report'      => 'seo-report',
'download'    => 'json',
'snapshot_id' => $report['id'],
),
admin_url( 'admin.php' )
),
'wpshadow_download_seo_report',
'nonce'
),
),
array(
'label' => __( 'Download PDF', 'wpshadow' ),
'url'   => wp_nonce_url(
add_query_arg(
array(
'page'        => 'wpshadow-reports',
'report'      => 'seo-report',
'download'    => 'pdf',
'snapshot_id' => $report['id'],
),
admin_url( 'admin.php' )
),
'wpshadow_download_seo_report',
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

// Calculate SEO score
$seo_score       = 100;
$critical_issues = 0;
$moderate_issues = 0;

foreach ( $seo_findings as $finding ) {
$severity = $finding['severity'] ?? 'low';
if ( in_array( $severity, array( 'critical', 'high' ), true ) ) {
$seo_score -= 10;
$critical_issues++;
} elseif ( 'medium' === $severity ) {
$seo_score -= 5;
$moderate_issues++;
} else {
$seo_score -= 2;
}
}

$seo_score = max( 0, min( 100, $seo_score ) );

if ( $seo_score >= 80 ) {
$seo_label   = __( 'Excellent', 'wpshadow' );
$seo_message = __( 'Your site is well-optimized for search engines.', 'wpshadow' );
$seo_color   = '#10b981';
} elseif ( $seo_score >= 60 ) {
$seo_label   = __( 'Good', 'wpshadow' );
$seo_message = __( 'Your site has good SEO with some room for improvement.', 'wpshadow' );
$seo_color   = '#f59e0b';
} else {
$seo_label   = __( 'Needs improvement', 'wpshadow' );
$seo_message = __( 'Several SEO issues could be affecting your search visibility.', 'wpshadow' );
$seo_color   = '#ef4444';
}

?>

<div class="wrap wps-page-container">
<?php
wpshadow_render_page_header(
__( 'SEO Report', 'wpshadow' ),
__( 'Analyze your site\'s search engine optimization and discover opportunities to improve visibility.', 'wpshadow' ),
'dashicons-search'
);
?>

<style>
.wps-seo-columns {
display: flex;
gap: var(--wps-space-4);
align-items: stretch;
flex-wrap: wrap;
}

.wps-seo-main {
flex: 2 1 520px;
}

.wps-seo-side {
flex: 1 1 320px;
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

<div class="wps-seo-columns wps-mb-4">
<div class="wps-seo-main">
<div class="wps-card wps-mb-4">
<div class="wps-card-body">
<h3 class="wps-text-lg wps-mb-2">
<?php esc_html_e( 'Report Options', 'wpshadow' ); ?>
</h3>
<p class="wps-text-sm wps-text-muted wps-mb-3">
<?php esc_html_e( 'Generate a comprehensive SEO report for your entire site.', 'wpshadow' ); ?>
</p>
<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" id="seo-report-form">
<input type="hidden" name="page" value="wpshadow-reports" />
<input type="hidden" name="report" value="seo-report" />
<input type="hidden" name="run_report" value="1" />
<?php wp_nonce_field( 'wpshadow_refresh_seo_reports', 'wpshadow_refresh_seo_reports_nonce' ); ?>
<button class="wps-btn wps-btn--primary" type="submit">
<span class="dashicons dashicons-search"></span>
<?php esc_html_e( 'Run SEO Analysis', 'wpshadow' ); ?>
</button>
</form>

<script>
(function() {
'use strict';

var form = document.getElementById('seo-report-form');
if (!form) {
return;
}

form.addEventListener('submit', function(e) {
e.preventDefault();

var nonceField = form.querySelector('input[name="wpshadow_refresh_seo_reports_nonce"]');
if (!nonceField) {
alert('<?php echo esc_js( __( 'Security check failed', 'wpshadow' ) ); ?>');
return;
}

// Show or create progress card
var progressCard = document.getElementById('seo-report-progress');
if (!progressCard) {
progressCard = document.createElement('div');
progressCard.id = 'seo-report-progress';
progressCard.setAttribute('data-run', '1');
progressCard.className = 'wps-card wps-mb-4';
progressCard.innerHTML = `
<div class="wps-card-body">
<h3 class="wps-text-lg wps-mb-2">
<?php esc_html_e( 'Analysis Progress', 'wpshadow' ); ?>
</h3>
<p class="wps-text-sm wps-text-muted wps-mb-3" id="seo-report-progress-message">
<?php esc_html_e( 'Analyzing your site\'s SEO...', 'wpshadow' ); ?>
</p>
<div class="wps-report-progress" role="status" aria-live="polite">
<div class="wps-report-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
<div class="wps-report-progress-fill" id="seo-report-progress-fill"></div>
</div>
<ul class="wps-report-progress-list" id="seo-report-progress-steps">
<li data-step="diagnostics">
<span class="wps-report-progress-status">1</span>
<?php esc_html_e( 'Running SEO diagnostics', 'wpshadow' ); ?>
</li>
<li data-step="metadata">
<span class="wps-report-progress-status">2</span>
<?php esc_html_e( 'Analyzing meta tags', 'wpshadow' ); ?>
</li>
<li data-step="content">
<span class="wps-report-progress-status">3</span>
<?php esc_html_e( 'Checking content structure', 'wpshadow' ); ?>
</li>
<li data-step="technical">
<span class="wps-report-progress-status">4</span>
<?php esc_html_e( 'Reviewing technical SEO', 'wpshadow' ); ?>
</li>
<li data-step="snapshot">
<span class="wps-report-progress-status">5</span>
<?php esc_html_e( 'Saving report snapshot', 'wpshadow' ); ?>
</li>
</ul>
</div>
</div>
`;
form.parentNode.insertBefore(progressCard, form.nextSibling);
}

progressCard.style.display = 'block';
var progressFill = document.getElementById('seo-report-progress-fill');
var progressMessage = document.getElementById('seo-report-progress-message');
var progressSteps = document.getElementById('seo-report-progress-steps');
var reportContent = document.getElementById('seo-report-content');
if (!progressFill || !progressMessage || !progressSteps) {
form.submit();
return;
}

if (reportContent) {
reportContent.classList.add('wps-report-progress-content-hidden');
}

jQuery.post('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
action: 'wpshadow_run_seo_report',
nonce: nonceField.value
}).done(function(response) {
runProgressAnimation(progressCard, progressFill, progressMessage, progressSteps, reportContent);
}).fail(function() {
form.submit();
});
});

function runProgressAnimation(card, fill, message, steps, content) {
var stepsList = Array.prototype.slice.call(steps.querySelectorAll('li'));
var currentIndex = 0;
var progressValue = 5;
var stepMessages = [
'<?php echo esc_js( __( 'Analyzing your site\'s SEO...', 'wpshadow' ) ); ?>',
'<?php echo esc_js( __( 'Checking meta tags and structure...', 'wpshadow' ) ); ?>',
'<?php echo esc_js( __( 'Reviewing content optimization...', 'wpshadow' ) ); ?>',
'<?php echo esc_js( __( 'Finalizing SEO report...', 'wpshadow' ) ); ?>'
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
message.textContent = '<?php echo esc_js( __( 'SEO report ready.', 'wpshadow' ) ); ?>';
refreshPastReports();
if (content) {
content.classList.remove('wps-report-progress-content-hidden');
}
}
}, 700);
}

function refreshPastReports() {
var nonceField = form.querySelector('input[name="wpshadow_refresh_seo_reports_nonce"]');
if (!nonceField) {
return;
}
jQuery.post('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
action: 'wpshadow_refresh_seo_reports',
nonce: nonceField.value
}).done(function(response) {
if (response && response.success && response.data && response.data.html) {
var pastReportsContainer = document.getElementById('seo-past-reports');
if (pastReportsContainer) {
pastReportsContainer.innerHTML = response.data.html;
}
}
});
}
})();
</script>
</div>
</div>

<?php if ( $show_progress ) : ?>
<div class="wps-card wps-mb-4" id="seo-report-progress" data-run="1">
<div class="wps-card-body">
<h3 class="wps-text-lg wps-mb-2">
<?php esc_html_e( 'Analysis Progress', 'wpshadow' ); ?>
</h3>
<p class="wps-text-sm wps-text-muted wps-mb-3" id="seo-report-progress-message">
<?php esc_html_e( 'Analyzing your site\'s SEO...', 'wpshadow' ); ?>
</p>
<div class="wps-report-progress" role="status" aria-live="polite">
<div class="wps-report-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
<div class="wps-report-progress-fill" id="seo-report-progress-fill"></div>
</div>
<ul class="wps-report-progress-list" id="seo-report-progress-steps">
<li data-step="diagnostics">
<span class="wps-report-progress-status">1</span>
<?php esc_html_e( 'Running SEO diagnostics', 'wpshadow' ); ?>
</li>
<li data-step="metadata">
<span class="wps-report-progress-status">2</span>
<?php esc_html_e( 'Analyzing meta tags', 'wpshadow' ); ?>
</li>
<li data-step="content">
<span class="wps-report-progress-status">3</span>
<?php esc_html_e( 'Checking content structure', 'wpshadow' ); ?>
</li>
<li data-step="technical">
<span class="wps-report-progress-status">4</span>
<?php esc_html_e( 'Reviewing technical SEO', 'wpshadow' ); ?>
</li>
<li data-step="snapshot">
<span class="wps-report-progress-status">5</span>
<?php esc_html_e( 'Saving report snapshot', 'wpshadow' ); ?>
</li>
</ul>
</div>
</div>
</div>
<?php endif; ?>

<?php if ( $show_progress ) : ?>
<script>
(function() {
var progressCard = document.getElementById('seo-report-progress');
if (!progressCard) {
return;
}
var progressFill = document.getElementById('seo-report-progress-fill');
var progressMessage = document.getElementById('seo-report-progress-message');
var progressSteps = document.getElementById('seo-report-progress-steps');
var reportContent = document.getElementById('seo-report-content');
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
'<?php echo esc_js( __( 'Analyzing your site\'s SEO...', 'wpshadow' ) ); ?>',
'<?php echo esc_js( __( 'Checking meta tags and structure...', 'wpshadow' ) ); ?>',
'<?php echo esc_js( __( 'Reviewing content optimization...', 'wpshadow' ) ); ?>',
'<?php echo esc_js( __( 'Finalizing SEO report...', 'wpshadow' ) ); ?>'
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
progressMessage.textContent = '<?php echo esc_js( __( 'SEO report ready.', 'wpshadow' ) ); ?>';
if (reportContent) {
reportContent.classList.remove('wps-report-progress-content-hidden');
}
}
}, 700);

if (window.history && window.history.replaceState) {
var url = new URL(window.location.href);
url.searchParams.delete('run_report');
// In proxied/dev-container environments, admin_url() can point to
// a different origin (e.g. localhost). Only replace history when
// the target URL matches the current document origin.
if (url.origin === window.location.origin) {
window.history.replaceState({}, document.title, url.toString());
}
}
})();
</script>
<?php endif; ?>

<div id="seo-report-content" class="wps-report-progress-content">
<div class="wps-card">
<div class="wps-card-body">
<h2 class="wps-text-xl wps-mb-3">
<span class="dashicons dashicons-search wps-text-primary"></span>
<?php esc_html_e( 'SEO Overview', 'wpshadow' ); ?>
</h2>
<p class="wps-text-muted wps-mb-3">
<?php esc_html_e( 'This report analyzes your site\'s search engine optimization and identifies opportunities to improve visibility.', 'wpshadow' ); ?>
</p>

<div class="wps-grid wps-grid-cols-2 wps-gap-3 wps-mb-4">
<div class="wps-card">
<div class="wps-card-body">
<div class="wps-flex wps-items-center wps-gap-2">
<span class="dashicons dashicons-chart-area wps-text-2xl wps-text-success"></span>
<div>
<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'SEO Score', 'wpshadow' ); ?></div>
<div class="wps-text-lg wps-font-semibold"><?php echo esc_html( $seo_score . '/100' ); ?></div>
</div>
</div>
</div>
</div>

<div class="wps-card">
<div class="wps-card-body">
<div class="wps-flex wps-items-center wps-gap-2">
<span class="dashicons dashicons-warning wps-text-2xl wps-text-error"></span>
<div>
<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Issues Found', 'wpshadow' ); ?></div>
<div class="wps-text-lg wps-font-semibold"><?php echo esc_html( number_format_i18n( count( $seo_findings ) ) ); ?></div>
</div>
</div>
</div>
</div>

<div class="wps-card">
<div class="wps-card-body">
<div class="wps-flex wps-items-center wps-gap-2">
<span class="dashicons dashicons-admin-plugins wps-text-2xl wps-text-info"></span>
<div>
<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'SEO Plugins', 'wpshadow' ); ?></div>
<div class="wps-text-lg wps-font-semibold"><?php echo esc_html( number_format_i18n( count( $seo_plugins ) ) ); ?></div>
</div>
</div>
</div>
</div>

<div class="wps-card">
<div class="wps-card-body">
<div class="wps-flex wps-items-center wps-gap-2">
<span class="dashicons dashicons-admin-site wps-text-2xl wps-text-primary"></span>
<div>
<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Sitemap', 'wpshadow' ); ?></div>
<div class="wps-text-sm wps-font-semibold">
<?php echo $report_summary['has_sitemap'] ? esc_html__( 'Active', 'wpshadow' ) : esc_html__( 'Missing', 'wpshadow' ); ?>
</div>
</div>
</div>
</div>
</div>
</div>

<?php if ( ! empty( $seo_findings ) ) : ?>
<div class="wps-card wps-mb-4">
<div class="wps-card-body">
<h3 class="wps-text-lg wps-mb-3"><?php esc_html_e( 'SEO Issues', 'wpshadow' ); ?></h3>
<ul class="wps-list-disc wps-ml-5">
<?php foreach ( array_slice( $seo_findings, 0, 10 ) as $finding ) : ?>
<li class="wps-mb-2">
<strong><?php echo esc_html( $finding['title'] ?? __( 'SEO Issue', 'wpshadow' ) ); ?></strong>
<span class="wps-text-xs wps-text-muted wps-block">
<?php echo esc_html( $finding['description'] ?? '' ); ?>
</span>
</li>
<?php endforeach; ?>
<?php if ( count( $seo_findings ) > 10 ) : ?>
<li class="wps-text-muted">
<?php
/* translators: %d: number of additional issues */
echo esc_html( sprintf( __( '+ %d more issues', 'wpshadow' ), count( $seo_findings ) - 10 ) );
?>
</li>
<?php endif; ?>
</ul>
</div>
</div>
<?php else : ?>
<div class="notice notice-success wps-mb-4">
<p>
<span class="dashicons dashicons-yes-alt"></span>
<?php esc_html_e( 'Great news! No SEO issues found.', 'wpshadow' ); ?>
</p>
</div>
<?php endif; ?>
</div>
</div>

<div class="wps-card wps-mt-4">
<div class="wps-card-body">
<h3 class="wps-text-lg wps-mb-3">
<?php esc_html_e( 'What This Analysis Covers', 'wpshadow' ); ?>
</h3>
<div class="wps-grid wps-grid-cols-1 wps-gap-4">
<div>
<h4 class="wps-font-semibold wps-mb-2">
<span class="dashicons dashicons-search"></span>
<?php esc_html_e( 'On-Page SEO', 'wpshadow' ); ?>
</h4>
<ul class="wps-list-disc wps-ml-5 wps-text-sm">
<li><?php esc_html_e( 'Title tags and meta descriptions', 'wpshadow' ); ?></li>
<li><?php esc_html_e( 'Heading structure (H1, H2, H3)', 'wpshadow' ); ?></li>
<li><?php esc_html_e( 'Image alt text coverage', 'wpshadow' ); ?></li>
</ul>
</div>
<div>
<h4 class="wps-font-semibold wps-mb-2">
<span class="dashicons dashicons-admin-site"></span>
<?php esc_html_e( 'Technical SEO', 'wpshadow' ); ?>
</h4>
<ul class="wps-list-disc wps-ml-5 wps-text-sm">
<li><?php esc_html_e( 'Sitemap availability', 'wpshadow' ); ?></li>
<li><?php esc_html_e( 'Robots.txt configuration', 'wpshadow' ); ?></li>
<li><?php esc_html_e( 'Canonical URL usage', 'wpshadow' ); ?></li>
</ul>
</div>
<div>
<h4 class="wps-font-semibold wps-mb-2">
<span class="dashicons dashicons-admin-plugins"></span>
<?php esc_html_e( 'SEO Plugins & Schema', 'wpshadow' ); ?>
</h4>
<ul class="wps-list-disc wps-ml-5 wps-text-sm">
<li><?php esc_html_e( 'Active SEO plugin detection', 'wpshadow' ); ?></li>
<li><?php esc_html_e( 'Structured data presence', 'wpshadow' ); ?></li>
<li><?php esc_html_e( 'Open Graph tag coverage', 'wpshadow' ); ?></li>
</ul>
</div>
</div>
</div>
</div>
</div>
</div>

<div class="wps-seo-side">
<div class="wps-card">
<div class="wps-card-body">
<h3 class="wps-text-lg wps-mb-2">
<?php esc_html_e( 'SEO Health', 'wpshadow' ); ?>
</h3>
<p class="wps-text-sm wps-text-muted wps-mb-3">
<?php esc_html_e( 'Overall search engine optimization health score.', 'wpshadow' ); ?>
</p>
<svg width="200" height="200" viewBox="0 0 200 200" class="wps-health-gauge-svg" aria-labelledby="seo-score-title" role="img">
<title id="seo-score-title">
<?php
echo esc_html(
sprintf(
/* translators: %d: SEO health score percentage */
__( 'SEO health score: %d%%', 'wpshadow' ),
$seo_score
)
);
?>
</title>
<!-- Outer decorative circle -->
<circle cx="100" cy="100" r="95" fill="none" stroke="<?php echo esc_attr( $seo_color ); ?>" stroke-width="2" opacity="0.2" />
<!-- Gauge background -->
<circle cx="100" cy="100" r="85" fill="none" stroke="#e0e0e0" stroke-width="16" />
<!-- Gauge progress -->
<circle cx="100" cy="100" r="85" fill="none" stroke="<?php echo esc_attr( $seo_color ); ?>" stroke-width="16"
stroke-dasharray="<?php echo (int) ( $seo_score / 100 * 534 ); ?> 534"
stroke-linecap="round" transform="rotate(-90 100 100)"
class="wps-gauge-progress" />
<!-- Center text -->
<text x="100" y="110" text-anchor="middle" font-size="48" font-weight="bold" fill="<?php echo esc_attr( $seo_color ); ?>"><?php echo esc_html( (int) $seo_score ); ?>%</text>
<text x="100" y="135" text-anchor="middle" font-size="16" fill="#666"><?php echo esc_html( $seo_label ); ?></text>
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
<p class="wps-health-gauge-message wps-mt-4"><?php echo esc_html( $seo_message ); ?></p>
</div>
</div>

<div id="seo-past-reports">
<?php
if ( function_exists( 'wpshadow_render_past_reports_card' ) ) {
wpshadow_render_past_reports_card(
array(
'title'         => __( 'Past Reports', 'wpshadow' ),
'description'   => __( 'Download previous SEO reports for comparison.', 'wpshadow' ),
'empty_message' => __( 'No past SEO reports yet.', 'wpshadow' ),
'items'         => $past_reports_items,
'pagination'    => array(
'current'  => $past_reports_page,
'total'    => $past_reports_pages,
'param'    => 'seo_past_page',
'base_url' => add_query_arg(
array(
'page'   => 'wpshadow-reports',
'report' => 'seo-report',
),
admin_url( 'admin.php' )
),
),
'delete_action' => current_user_can( 'manage_options' ) && ! empty( $past_reports_items )
? array(
'action_url'   => admin_url( 'admin-post.php' ),
'nonce_action' => 'wpshadow_delete_seo_reports',
'nonce_name'   => 'wpshadow_delete_seo_reports_nonce',
'fields'       => array(
'action' => 'wpshadow_delete_seo_reports',
),
'label'        => __( 'Delete All Reports', 'wpshadow' ),
'confirm'      => __( 'Delete all past SEO reports? This cannot be undone.', 'wpshadow' ),
)
: array(),
)
);
}
?>
</div>
</div>
</div>
</div>
