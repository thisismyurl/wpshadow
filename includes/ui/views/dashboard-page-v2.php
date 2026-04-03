<?php
/**
 * WPShadow Dashboard & Guardian Pages
 *
 * Dashboard = reporting screen (what Guardian is doing)
 * Guardian  = management screen (all diagnostics + detail drill-down)
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Core\Form_Param_Helper;

/* ============================================================
   DASHBOARD — Reporting Screen
   ============================================================ */

/**
 * Render the WPShadow Dashboard (report view).
 *
 * Summarises what Guardian is monitoring. Clicking through on any
 * family card or issue row opens Guardian filtered to that area.
 *
 * @since 0.6094.0100
 */
function wpshadow_render_dashboard_v2() {
	$rows = wpshadow_get_diagnostics_activity_rows();
	$scan_lock = get_transient( 'wpshadow_scan_running' );
	$scan_running = false !== $scan_lock;

	$total    = count( $rows );
	$passed   = 0;
	$failed   = 0;
	$disabled = 0;
	$pending  = 0;

	foreach ( $rows as $row ) {
		$s          = isset( $row['status_raw'] ) ? (string) $row['status_raw'] : '';
		$is_enabled = ! empty( $row['enabled'] );
		$last_run   = isset( $row['last_run_ts'] ) ? (int) $row['last_run_ts'] : 0;

		if ( 'disabled' === $s || ! $is_enabled ) {
			$disabled++;
			continue;
		}

		if ( 'passed' === $s ) {
			$passed++;
			continue;
		}

		if ( 'failed' === $s ) {
			$failed++;
			continue;
		}

		// Status has not reported as pass/fail yet.
		if ( $last_run <= 0 || ! in_array( $s, array( 'passed', 'failed' ), true ) ) {
			$pending++;
		}
	}

	$active      = max( 0, $total - $disabled );
	$score       = $active > 0 ? (int) round( ( $passed / $active ) * 100 ) : 100;
	$score_class = $score >= 80
		? 'wps-stat-value--pass'
		: ( $score >= 60 ? 'wps-stat-value--warn' : 'wps-stat-value--fail' );
	$failed_class = $failed > 0 ? 'wps-stat-value--fail' : 'wps-stat-value--muted';

	// Per-family health stats.
	$family_icons = array(
		'security'         => '🛡️',
		'performance'      => '⚡',
		'seo'              => '🔍',
		'accessibility'    => '♿',
		'database'         => '🗄️',
		'wordpress-health' => '💊',
		'design'           => '🎨',
		'settings'         => '⚙️',
		'monitoring'       => '📡',
		'code-quality'     => '🧹',
		'workflows'        => '🔄',
	);

	$family_data = array();
	foreach ( $rows as $row ) {
		$fam = isset( $row['family'] ) && '' !== (string) $row['family']
			? sanitize_key( (string) $row['family'] )
			: 'other';
		if ( ! isset( $family_data[ $fam ] ) ) {
			$family_data[ $fam ] = array( 'passed' => 0, 'failed' => 0, 'disabled' => 0, 'total' => 0 );
		}
		$family_data[ $fam ]['total']++;

		$s          = isset( $row['status_raw'] ) ? (string) $row['status_raw'] : '';
		$is_enabled = ! empty( $row['enabled'] );
		$last_run   = isset( $row['last_run_ts'] ) ? (int) $row['last_run_ts'] : 0;

		if ( 'disabled' === $s || ! $is_enabled ) {
			$family_data[ $fam ]['disabled']++;
			continue;
		}

		if ( $last_run <= 0 || ! in_array( $s, array( 'passed', 'failed' ), true ) ) {
			continue;
		}

		if ( 'passed' === $s ) {
			$family_data[ $fam ]['passed']++;
		} elseif ( 'failed' === $s ) {
			$family_data[ $fam ]['failed']++;
		}
	}

	// Sort: families with failures first.
	uasort( $family_data, function( $a, $b ) {
		if ( $a['failed'] !== $b['failed'] ) {
			return $b['failed'] - $a['failed'];
		}
		return 0;
	} );

	// Failing checks: core first, then rest.
	$failing = array_values( array_filter( $rows, function( $r ) {
		$status     = isset( $r['status_raw'] ) ? (string) $r['status_raw'] : '';
		$last_run   = isset( $r['last_run_ts'] ) ? (int) $r['last_run_ts'] : 0;
		$is_enabled = ! empty( $r['enabled'] );

		return $is_enabled && $last_run > 0 && 'failed' === $status;
	} ) );
	usort( $failing, function( $a, $b ) {
		return (int) ! empty( $b['is_core'] ) - (int) ! empty( $a['is_core'] );
	} );
	$top_issues   = array_slice( $failing, 0, 8 );
	$extra_issues = max( 0, count( $failing ) - 8 );

	$guardian_url = admin_url( 'admin.php?page=wpshadow-guardian' );
	?>

	<div class="wrap wpshadow-dashboard wps-page-container">

		<!-- Page Header -->
		<div class="wps-page-header">
			<div class="wps-page-header-icon">📊</div>
			<div class="wps-page-header-content">
				<h1><?php esc_html_e( 'Dashboard', 'wpshadow' ); ?></h1>
				<p><?php esc_html_e( "A live summary of your site's health. Use Guardian to manage individual checks.", 'wpshadow' ); ?></p>
			</div>
			<div class="wps-page-actions wps-page-actions--header">
				<a href="<?php echo esc_url( $guardian_url ); ?>" class="wps-button wps-button--secondary">
					<?php esc_html_e( 'Open Guardian →', 'wpshadow' ); ?>
				</a>
			</div>
		</div>

		<!-- Stats Row -->
		<div class="wps-grid wps-grid--3col wps-summary-grid">
			<div class="wps-stat">
				<div id="wps-dashboard-score-value" class="wps-stat-value <?php echo esc_attr( $score_class ); ?>"><?php echo esc_html( $score ); ?>%</div>
				<div class="wps-stat-label"><?php esc_html_e( 'Health Score', 'wpshadow' ); ?></div>
			</div>
			<div class="wps-stat">
				<div id="wps-dashboard-passed-value" class="wps-stat-value wps-stat-value--pass"><?php echo esc_html( $passed ); ?></div>
				<div class="wps-stat-label"><?php esc_html_e( 'Checks Passed', 'wpshadow' ); ?></div>
			</div>
			<div class="wps-stat">
				<div id="wps-dashboard-failed-value" class="wps-stat-value <?php echo esc_attr( $failed_class ); ?>"><?php echo esc_html( $failed ); ?></div>
				<div class="wps-stat-label"><?php esc_html_e( 'Issues Found', 'wpshadow' ); ?></div>
			</div>
		</div>

		<?php if ( $pending > 0 && ! $scan_running ) : ?>
		<div id="wps-pending-tests-alert" class="wps-alert wps-alert--warning wps-mb-8">
			<div class="wps-alert-icon">🧪</div>
			<div class="wps-alert-content">
				<strong>
					<?php
					printf(
						/* translators: %d: pending diagnostic count */
						esc_html( _n( '%d check is still waiting to run.', '%d checks are still waiting to run.', $pending, 'wpshadow' ) ),
						(int) $pending
					);
					?>
				</strong>
				<p class="wps-alert-copy">
					<?php esc_html_e( 'WPShadow only shows a final all-clear after every enabled test has reported a real result. Open Guardian to run diagnostics from the detail panels.', 'wpshadow' ); ?>
				</p>
				<p class="wps-alert-action">
					<a href="<?php echo esc_url( $guardian_url ); ?>" class="wps-button wps-button--primary">
						<?php esc_html_e( 'Open Guardian', 'wpshadow' ); ?>
					</a>
				</p>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $top_issues ) ) : ?>
		<!-- Attention Needed -->
		<div id="wps-dashboard-attention-card" class="wps-card wps-card--danger-accent" data-guardian-url="<?php echo esc_url( $guardian_url ); ?>">
			<div class="wps-card-header">
				<h2 id="wps-dashboard-attention-title" class="wps-card-title wps-card-title--danger">
					<?php
					printf(
						/* translators: %d: number of issues */
						esc_html( _n( '⚠️ %d Issue Needs Attention', '⚠️ %d Issues Need Attention', count( $failing ), 'wpshadow' ) ),
						(int) count( $failing )
					);
					?>
				</h2>
				<p class="wps-alert-copy wps-text-gray-600">
					<?php esc_html_e( 'Core checks are listed first — they have the highest confidence and impact.', 'wpshadow' ); ?>
				</p>
			</div>
			<div class="wps-card-body wps-card-body--tight-top">
				<table class="wps-attention-table">
					<tbody id="wps-dashboard-attention-tbody">
					<?php foreach ( $top_issues as $issue ) :
						$issue_name    = isset( $issue['name'] ) ? (string) $issue['name'] : '';
						$issue_reason  = isset( $issue['failure_reason'] ) ? (string) $issue['failure_reason'] : '';
						$issue_url     = isset( $issue['detail_url'] ) ? (string) $issue['detail_url'] : '#';
						$issue_is_core = ! empty( $issue['is_core'] );
						$issue_family  = isset( $issue['family'] ) ? sanitize_key( (string) $issue['family'] ) : '';
					?>
					<tr class="wps-attention-row">
						<td class="wps-attention-cell-icon">
							<span class="wps-attention-icon">✕</span>
						</td>
						<td class="wps-attention-cell-main">
							<div class="wps-attention-inline">
								<span class="wps-attention-label"><?php echo esc_html( $issue_name ); ?></span>
								<?php if ( $issue_is_core ) : ?>
									<span class="wps-badge wps-badge--core"><?php esc_html_e( 'Core', 'wpshadow' ); ?></span>
								<?php endif; ?>
								<?php if ( '' !== $issue_family ) : ?>
									<span class="wps-badge wps-badge--subtle">
										<?php echo esc_html( ucwords( str_replace( '-', ' ', $issue_family ) ) ); ?>
									</span>
								<?php endif; ?>
							</div>
							<?php if ( '' !== $issue_reason ) : ?>
								<p class="wps-attention-reason">
									<?php echo esc_html( $issue_reason ); ?>
								</p>
							<?php endif; ?>
						</td>
						<td class="wps-attention-cell-actions">
							<a href="<?php echo esc_url( $issue_url ); ?>" class="wps-button wps-button--secondary wps-button--sm">
								<?php esc_html_e( 'Details →', 'wpshadow' ); ?>
							</a>
						</td>
					</tr>
					<?php endforeach; ?>
					<?php if ( $extra_issues > 0 ) : ?>
					<tr class="wps-attention-row">
						<td colspan="3" class="wps-table-summary-row">
							<?php
							printf(
								/* translators: %d: additional issue count */
								esc_html( _n( '+%d more issue', '+%d more issues', $extra_issues, 'wpshadow' ) ),
								(int) $extra_issues
							);
							?> —
							<a href="<?php echo esc_url( $guardian_url ); ?>"><?php esc_html_e( 'View all in Guardian', 'wpshadow' ); ?></a>
						</td>
					</tr>
					<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php elseif ( 0 === $pending && $active > 0 ) : ?>
		<!-- All Clear -->
		<div class="wps-alert wps-alert--info wps-mb-8">
			<div class="wps-alert-icon">✓</div>
			<div class="wps-alert-content">
				<strong><?php esc_html_e( 'All checks passed!', 'wpshadow' ); ?></strong>
				<p class="wps-alert-copy">
					<?php esc_html_e( 'No issues found on the last run. Guardian will alert you if anything changes.', 'wpshadow' ); ?>
				</p>
			</div>
		</div>
		<?php elseif ( $active <= 0 ) : ?>
		<div class="wps-alert wps-alert--info wps-mb-8">
			<div class="wps-alert-icon">ℹ️</div>
			<div class="wps-alert-content">
				<strong><?php esc_html_e( 'No enabled tests are reporting yet.', 'wpshadow' ); ?></strong>
				<p class="wps-alert-copy">
					<?php esc_html_e( 'Enable at least one check and run a scan to see dashboard feedback.', 'wpshadow' ); ?>
				</p>
			</div>
		</div>
		<?php endif; ?>

		<!-- Health by Area -->
		<h2 class="wps-section-title">
			<?php esc_html_e( 'Health by Area', 'wpshadow' ); ?>
		</h2>
		<div class="wps-grid wps-grid--auto">
			<?php foreach ( $family_data as $fam_slug => $fam ) :
				$fam_active = $fam['total'] - $fam['disabled'];
				$fam_score  = $fam_active > 0 ? (int) round( ( $fam['passed'] / $fam_active ) * 100 ) : 100;
				$fam_score_class = $fam['failed'] > 0
					? ( $fam_score < 60 ? 'wps-family-card-score--fail' : 'wps-family-card-score--warn' )
					: 'wps-family-card-score--pass';
				$fam_icon        = isset( $family_icons[ $fam_slug ] ) ? $family_icons[ $fam_slug ] : '📋';
			?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&family=' . rawurlencode( $fam_slug ) ) ); ?>"
			   class="wps-card wps-card--interactive"
			   data-family-card="<?php echo esc_attr( $fam_slug ); ?>">
				<div class="wps-family-card-head">
					<div class="wps-family-card-icon"><?php echo esc_html( $fam_icon ); ?></div>
					<div class="wps-family-card-label">
						<?php echo esc_html( ucwords( str_replace( '-', ' ', $fam_slug ) ) ); ?>
					</div>
				</div>
				<div data-family-score="<?php echo esc_attr( $fam_slug ); ?>" class="wps-family-card-score <?php echo esc_attr( $fam_score_class ); ?>">
					<?php echo esc_html( $fam_score ); ?>%
				</div>
				<div data-family-failed="<?php echo esc_attr( $fam_slug ); ?>" class="wps-family-card-meta wps-family-card-meta--spaced">
					<?php
					printf(
						/* translators: %d: failed checks count */
						esc_html( _n( '%d issue', '%d issues', (int) $fam['failed'], 'wpshadow' ) ),
						(int) $fam['failed']
					);
					?>
				</div>
				<div data-family-passed="<?php echo esc_attr( $fam_slug ); ?>" class="wps-family-card-meta">
					<?php
					printf(
						/* translators: %1$d: passed checks, %2$d: active checks */
						esc_html__( '%1$d / %2$d passed', 'wpshadow' ),
						(int) $fam['passed'],
						(int) $fam_active
					);
					?>
				</div>
			</a>
			<?php endforeach; ?>
		</div>

		<div class="wps-cta-row">
			<a href="<?php echo esc_url( $guardian_url ); ?>" class="wps-button wps-button--primary wps-button--lg">
				<?php esc_html_e( 'Open Full Guardian →', 'wpshadow' ); ?>
			</a>
		</div>

	</div>

	<?php
}


/* ============================================================
   GUARDIAN — Diagnostics Management Screen
   ============================================================ */

/**
 * Render Guardian diagnostics management page.
 *
 * @since 0.6094.0100
 */
function wpshadow_render_guardian_page() {
	$page_subview = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( (string) $_GET['view'] ) ) : '';
	$diagnostic_key = Form_Param_Helper::get( 'diagnostic', 'key', '' );

	if ( 'detail' === $page_subview || '' !== $diagnostic_key ) {
		wpshadow_render_diagnostic_detail_v2();
		return;
	}

	$rows = wpshadow_get_diagnostics_activity_rows();

	// Stats for the header strip.
	// Only count diagnostics that have actually run (passed or failed) so the
	// "X / Y passed" ratio is internally consistent with the issues number shown.
	// Pending (never-run) and disabled diagnostics are excluded from the denominator.
	$g_total    = 0;
	$g_passed   = 0;
	$g_failed   = 0;
	$g_pending  = 0;

	foreach ( $rows as $row ) {
		$s          = isset( $row['status_raw'] ) ? (string) $row['status_raw'] : '';
		$is_enabled = ! empty( $row['enabled'] );
		$last_run   = isset( $row['last_run_ts'] ) ? (int) $row['last_run_ts'] : 0;

		if ( ! $is_enabled || 'disabled' === $s ) {
			continue;
		}

		if ( 'passed' === $s ) {
			$g_total++;
			$g_passed++;
		} elseif ( 'failed' === $s ) {
			$g_total++;
			$g_failed++;
		} else {
			// Enabled but not yet run.
			$g_pending++;
		}
	}

	// Unique families for the Area filter dropdown.
	$families = array();
	foreach ( $rows as $row ) {
		$fam = isset( $row['family'] ) ? sanitize_key( (string) $row['family'] ) : '';
		if ( '' !== $fam && ! isset( $families[ $fam ] ) ) {
			$families[ $fam ] = ucwords( str_replace( '-', ' ', $fam ) );
		}
	}
	ksort( $families );

	// Pre-select family filter from ?family= URL param (linked from Dashboard).
	$preselect_family = isset( $_GET['family'] ) ? sanitize_key( wp_unslash( (string) $_GET['family'] ) ) : '';
	$guardian_redirect = admin_url( 'admin.php?page=wpshadow-guardian' );
	$run_guardian_url  = class_exists( '\WPShadow\Admin\Stale_Diagnostics_Notice' )
		? \WPShadow\Admin\Stale_Diagnostics_Notice::get_run_guardian_url( $guardian_redirect )
		: wp_nonce_url( add_query_arg( array( 'action' => 'wpshadow_run_guardian', 'redirect' => $guardian_redirect ), admin_url( 'admin-post.php' ) ), 'wpshadow_run_guardian' );
	?>

	<?php
	// Sort: failed+core first, then failed, then the rest — most urgent checks are always visible first.
	usort( $rows, function ( $a, $b ) {
		$a_pri = ( 'failed' === ( $a['status_raw'] ?? '' ) ? 2 : 0 ) + ( ! empty( $a['is_core'] ) ? 1 : 0 );
		$b_pri = ( 'failed' === ( $b['status_raw'] ?? '' ) ? 2 : 0 ) + ( ! empty( $b['is_core'] ) ? 1 : 0 );
		return $b_pri - $a_pri;
	} );

	// Snapshot fixability metadata once so cards and priority queue stay consistent.
	$fixability_by_run_key = array();
	if ( class_exists( '\\WPShadow\\Treatments\\Treatment_Registry' ) ) {
		foreach ( $rows as $row ) {
			$run_key = isset( $row['run_key'] ) ? sanitize_key( (string) $row['run_key'] ) : '';
			if ( '' === $run_key ) {
				continue;
			}

			$fix_info = array(
				'has_treatment' => false,
				'maturity'      => 'none',
				'risk_level'    => '',
				'fix_label'     => __( 'Manual fix', 'wpshadow' ),
			);

			$tx_class = \WPShadow\Treatments\Treatment_Registry::get_treatment( $run_key );
			if ( null !== $tx_class ) {
				$fix_info['has_treatment'] = true;
				$finding_id                = method_exists( $tx_class, 'get_finding_id' )
					? sanitize_key( (string) $tx_class::get_finding_id() )
					: $run_key;
				$tx_meta                   = class_exists( '\\WPShadow\\Core\\Treatment_Metadata' )
					? \WPShadow\Core\Treatment_Metadata::get( $finding_id )
					: null;
				$fix_info['maturity']      = isset( $tx_meta['maturity'] ) ? (string) $tx_meta['maturity'] : 'unknown';
				$fix_info['risk_level']    = isset( $tx_meta['risk_level'] ) ? (string) $tx_meta['risk_level'] : '';

				if ( 'shipped' === $fix_info['maturity'] ) {
					$fix_info['fix_label'] = __( 'Automated fix', 'wpshadow' );
				} elseif ( 'guidance' === $fix_info['maturity'] ) {
					$fix_info['fix_label'] = __( 'Guidance steps', 'wpshadow' );
				} else {
					$fix_info['fix_label'] = __( 'Treatment available', 'wpshadow' );
				}
			}

			$fixability_by_run_key[ $run_key ] = $fix_info;
		}
	}

	// Top-priority lane: failing checks ranked by confidence, core status, and fixability.
	$priority_queue = array();
	foreach ( $rows as $row ) {
		$status_raw = isset( $row['status_raw'] ) ? (string) $row['status_raw'] : 'unknown';
		if ( 'failed' !== $status_raw || empty( $row['enabled'] ) ) {
			continue;
		}

		$run_key    = isset( $row['run_key'] ) ? sanitize_key( (string) $row['run_key'] ) : '';
		$fix_info   = isset( $fixability_by_run_key[ $run_key ] ) ? $fixability_by_run_key[ $run_key ] : array(
			'maturity'   => 'none',
			'fix_label'  => __( 'Manual fix', 'wpshadow' ),
			'risk_level' => '',
		);
		$is_core    = ! empty( $row['is_core'] );
		$confidence = isset( $row['confidence'] ) ? (string) $row['confidence'] : 'standard';

		$score = 0;
		$score += $is_core ? 100 : 0;
		$score += ( 'high' === $confidence ) ? 30 : ( ( 'standard' === $confidence ) ? 20 : 10 );
		$score += ( 'shipped' === $fix_info['maturity'] ) ? 25 : ( ( 'guidance' === $fix_info['maturity'] ) ? 10 : 0 );

		$reason = __( 'Manual review is recommended for this issue.', 'wpshadow' );
		$recommended_action = __( 'Review and plan remediation.', 'wpshadow' );
		if ( $is_core && 'high' === $confidence ) {
			$reason = __( 'Core check with high confidence; address this first.', 'wpshadow' );
			$recommended_action = __( 'Fix now, then rerun immediately.', 'wpshadow' );
		} elseif ( 'shipped' === $fix_info['maturity'] ) {
			$reason = __( 'Automated fix is available right now.', 'wpshadow' );
			$recommended_action = __( 'Apply automated fix, then rerun.', 'wpshadow' );
		} elseif ( 'guidance' === $fix_info['maturity'] ) {
			$reason = __( 'Guided remediation steps are available.', 'wpshadow' );
			$recommended_action = __( 'Follow guidance steps, then rerun.', 'wpshadow' );
		} elseif ( 'low' === $confidence ) {
			$recommended_action = __( 'Validate manually before applying changes.', 'wpshadow' );
		}

		$priority_queue[] = array(
			'row'      => $row,
			'fix_info' => $fix_info,
			'score'    => $score,
			'reason'   => $reason,
			'action'   => $recommended_action,
		);
	}

	usort(
		$priority_queue,
		function ( $a, $b ) {
			return (int) $b['score'] - (int) $a['score'];
		}
	);
	$priority_queue = array_slice( $priority_queue, 0, 3 );
	?>

	<div class="wrap wpshadow-dashboard wps-page-container" id="wpshadow-guardian-app" data-preselected-family="<?php echo esc_attr( $preselect_family ); ?>">

		<!-- Page Header -->
		<div class="wps-page-header">
			<div class="wps-page-header-icon">🛡️</div>
			<div class="wps-page-header-content">
				<h1><?php esc_html_e( 'Guardian', 'wpshadow' ); ?></h1>
				<p><?php esc_html_e( 'Run diagnostics, apply automatic treatments, and refresh your site health report cards.', 'wpshadow' ); ?></p>
			</div>
			<div class="wps-page-actions wps-page-actions--header">
				<?php if ( $g_failed > 0 ) : ?>
					<span class="wps-header-status wps-header-status--fail">
						<?php
						printf(
							/* translators: %d: number of failing checks */
							esc_html( _n( '%d issue', '%d issues', $g_failed, 'wpshadow' ) ),
							(int) $g_failed
						);
						?>
					</span>
				<?php else : ?>
					<span class="wps-header-status wps-header-status--pass">
						<?php esc_html_e( '✓ All clear', 'wpshadow' ); ?>
					</span>
				<?php endif; ?>
				<span class="wps-header-summary">
					<?php
					printf(
						/* translators: %1$d: passed, %2$d: total run */
						esc_html__( '%1$d / %2$d passed', 'wpshadow' ),
						(int) $g_passed,
						(int) $g_total
					);
					?>
					<?php if ( $g_pending > 0 ) : ?>
						<span class="wps-badge wps-badge--subtle">
							<?php
							printf(
								/* translators: %d: not-yet-run count */
								esc_html( _n( '%d not yet run', '%d not yet run', $g_pending, 'wpshadow' ) ),
								(int) $g_pending
							);
							?>
						</span>
					<?php endif; ?>
				</span>
				<a href="<?php echo esc_url( $run_guardian_url ); ?>" class="wps-button wps-button--primary">
					<?php esc_html_e( 'Run Guardian', 'wpshadow' ); ?>
				</a>
			</div>
		</div>

		<?php if ( ! empty( $priority_queue ) ) : ?>
		<div class="wps-card wps-card--danger-accent">
			<div class="wps-card-header">
				<h2 class="wps-card-title"><?php esc_html_e( 'Start Here: Top 3 Priorities', 'wpshadow' ); ?></h2>
				<p class="wps-alert-copy wps-text-gray-600">
					<?php esc_html_e( 'Ordered by urgency: core failures, confidence, and fixability.', 'wpshadow' ); ?>
				</p>
			</div>
			<div class="wps-card-body">
				<ol class="wps-priority-list">
					<?php foreach ( $priority_queue as $entry ) :
						$item       = $entry['row'];
						$item_name  = isset( $item['name'] ) ? (string) $item['name'] : '';
						$item_url   = isset( $item['detail_url'] ) ? (string) $item['detail_url'] : '#';
						$item_core  = ! empty( $item['is_core'] );
						$item_conf  = isset( $item['confidence'] ) ? (string) $item['confidence'] : 'standard';
						$item_fix   = isset( $entry['fix_info'] ) && is_array( $entry['fix_info'] ) ? $entry['fix_info'] : array();
						$item_action = isset( $entry['action'] ) ? (string) $entry['action'] : '';
						$item_tokens = array();
						if ( $item_core ) {
							$item_tokens[] = __( 'Core', 'wpshadow' );
						}
						$item_tokens[] = ucfirst( $item_conf ) . ' ' . __( 'confidence', 'wpshadow' );
						$item_tokens[] = isset( $item_fix['fix_label'] ) ? (string) $item_fix['fix_label'] : __( 'Manual fix', 'wpshadow' );
						if ( ! empty( $item_fix['risk_level'] ) ) {
							$item_tokens[] = ucfirst( (string) $item_fix['risk_level'] ) . ' ' . __( 'risk', 'wpshadow' );
						}
					?>
					<li>
						<div class="wps-priority-item">
							<a href="<?php echo esc_url( $item_url ); ?>" class="wps-priority-link">
								<?php echo esc_html( $item_name ); ?>
							</a>
							<div class="wps-priority-meta">
								<?php echo esc_html( implode( ' • ', $item_tokens ) ); ?>
							</div>
							<div class="wps-priority-reason">
								<?php echo esc_html( (string) $entry['reason'] ); ?>
							</div>
							<?php if ( '' !== $item_action ) : ?>
							<div class="wps-priority-action">
								<strong><?php esc_html_e( 'Recommended action:', 'wpshadow' ); ?></strong>
								<?php echo esc_html( $item_action ); ?>
							</div>
							<?php endif; ?>
						</div>
					</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</div>
		<?php endif; ?>

		<!-- Filter Bar -->
		<div class="wps-filter-bar">
			<label class="wps-filter-label-grow">
				<span class="wps-text-sm"><?php esc_html_e( 'Search', 'wpshadow' ); ?></span>
				<input
					type="search"
					id="wps-search-diagnostics"
					placeholder="<?php echo esc_attr__( 'Find a diagnostic...', 'wpshadow' ); ?>"
					class="wps-filter-control wps-filter-control--full"
				/>
			</label>
			<div class="wps-filter-bar-spacer"></div>
			<label>
				<span class="wps-text-sm"><?php esc_html_e( 'Status', 'wpshadow' ); ?></span>
				<select id="wps-filter-result" class="wps-filter-control">
					<option value="all"><?php esc_html_e( 'All', 'wpshadow' ); ?></option>
					<option value="passed"><?php esc_html_e( 'Passed', 'wpshadow' ); ?></option>
					<option value="failed"><?php esc_html_e( 'Failed', 'wpshadow' ); ?></option>
					<option value="disabled"><?php esc_html_e( 'Disabled', 'wpshadow' ); ?></option>
				</select>
			</label>
			<label>
				<span class="wps-text-sm"><?php esc_html_e( 'Area', 'wpshadow' ); ?></span>
				<select id="wps-filter-family" class="wps-filter-control">
					<option value="all"><?php esc_html_e( 'All', 'wpshadow' ); ?></option>
					<?php foreach ( $families as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $preselect_family, $key ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
			<label>
				<span class="wps-text-sm"><?php esc_html_e( 'Confidence', 'wpshadow' ); ?></span>
				<select id="wps-filter-confidence" class="wps-filter-control">
					<option value="all"><?php esc_html_e( 'All', 'wpshadow' ); ?></option>
					<option value="high"><?php esc_html_e( 'High', 'wpshadow' ); ?></option>
					<option value="standard"><?php esc_html_e( 'Standard', 'wpshadow' ); ?></option>
					<option value="low"><?php esc_html_e( 'Low (Beta)', 'wpshadow' ); ?></option>
				</select>
			</label>
			<label class="wps-filter-checkbox-label">
				<input type="checkbox" id="wps-filter-core" />
				<span class="wps-text-sm"><?php esc_html_e( 'Core Only', 'wpshadow' ); ?></span>
			</label>
			<button id="wps-filter-clear" class="wps-button wps-button--secondary">
				<?php esc_html_e( 'Clear', 'wpshadow' ); ?>
			</button>
		</div>

		<!-- Diagnostics Grid -->
		<div class="wps-grid wps-grid--auto" id="wps-diagnostics-grid">
			<?php foreach ( $rows as $row ) :
				$url        = isset( $row['detail_url'] ) ? (string) $row['detail_url'] : '#';
				$name       = isset( $row['name'] ) ? (string) $row['name'] : '';
				$status_raw = isset( $row['status_raw'] ) ? (string) $row['status_raw'] : 'unknown';
				$family     = isset( $row['family'] ) ? sanitize_key( (string) $row['family'] ) : '';
				$confidence = isset( $row['confidence'] ) ? (string) $row['confidence'] : 'standard';
				$is_core    = ! empty( $row['is_core'] );
				$last_run   = isset( $row['last_run'] ) ? (string) $row['last_run'] : '';
				$enabled    = ! empty( $row['enabled'] );
				$run_key    = isset( $row['run_key'] ) ? sanitize_key( (string) $row['run_key'] ) : '';
				$fix_info   = isset( $fixability_by_run_key[ $run_key ] ) ? $fixability_by_run_key[ $run_key ] : array(
					'fix_label'  => __( 'Manual fix', 'wpshadow' ),
					'risk_level' => '',
				);

				$action_tokens = array();
				if ( 'failed' === $status_raw ) {
					$action_tokens[] = __( 'Issue Found', 'wpshadow' );
				} elseif ( 'passed' === $status_raw ) {
					$action_tokens[] = __( 'Passed', 'wpshadow' );
				} elseif ( 'disabled' === $status_raw ) {
					$action_tokens[] = __( 'Disabled', 'wpshadow' );
				} else {
					$action_tokens[] = __( 'Pending', 'wpshadow' );
				}
				if ( $is_core ) {
					$action_tokens[] = __( 'Core', 'wpshadow' );
				}
				$action_tokens[] = ucfirst( $confidence ) . ' ' . __( 'confidence', 'wpshadow' );
				$action_tokens[] = isset( $fix_info['fix_label'] ) ? (string) $fix_info['fix_label'] : __( 'Manual fix', 'wpshadow' );
				if ( ! empty( $fix_info['risk_level'] ) ) {
					$action_tokens[] = ucfirst( (string) $fix_info['risk_level'] ) . ' ' . __( 'risk', 'wpshadow' );
				}
				if ( 'low' === $confidence ) {
					$action_tokens[] = __( 'Advisory signal', 'wpshadow' );
				}
				$actionability_summary = implode( ' • ', $action_tokens );
			?>
			<a
				href="<?php echo esc_url( $url ); ?>"
				class="wps-diagnostic-card"
				data-diagnostic-row
				data-name="<?php echo esc_attr( strtolower( $name ) ); ?>"
				data-result="<?php echo esc_attr( $status_raw ); ?>"
				data-family="<?php echo esc_attr( $family ); ?>"
				data-confidence="<?php echo esc_attr( $confidence ); ?>"
				data-core="<?php echo esc_attr( $is_core ? 'yes' : 'no' ); ?>"
				data-enabled="<?php echo esc_attr( $enabled ? 'yes' : 'no' ); ?>"
			>
				<div class="wps-diagnostic-card-header">
					<h3 class="wps-diagnostic-card-title"><?php echo esc_html( $name ); ?></h3>
					<div class="wps-diagnostic-card-badges">
						<?php if ( $is_core ) : ?>
							<span class="wps-badge wps-badge--core"><?php esc_html_e( 'Core', 'wpshadow' ); ?></span>
						<?php endif; ?>
						<span class="wps-badge wps-badge--confidence-<?php echo esc_attr( $confidence ); ?>">
							<?php echo esc_html( ucfirst( $confidence ) ); ?>
						</span>
					</div>
				</div>
				<div class="wps-diagnostic-card-status">
					<?php if ( 'passed' === $status_raw ) : ?>
						<span class="wps-text-status-pass">✓</span>
						<span><?php esc_html_e( 'Passed', 'wpshadow' ); ?></span>
					<?php elseif ( 'failed' === $status_raw ) : ?>
						<span class="wps-text-status-fail">✕</span>
						<span><?php esc_html_e( 'Issue Found', 'wpshadow' ); ?></span>
					<?php elseif ( 'disabled' === $status_raw ) : ?>
						<span class="wps-text-gray-600">−</span>
						<span><?php esc_html_e( 'Disabled', 'wpshadow' ); ?></span>
					<?php else : ?>
						<span><?php esc_html_e( 'Pending', 'wpshadow' ); ?></span>
					<?php endif; ?>
				</div>
				<div class="wps-diagnostic-summary">
					<?php echo esc_html( $actionability_summary ); ?>
				</div>
				<div class="wps-diagnostic-card-footer">
					<span><?php echo wp_kses_post( $last_run ); ?></span>
					<span class="wps-diagnostic-arrow">→</span>
				</div>
			</a>
			<?php endforeach; ?>
		</div>

		<!-- No Results -->
		<div id="wps-no-results" class="wps-alert wps-alert--warning wps-hidden">
			<div class="wps-alert-icon">⚠️</div>
			<div class="wps-alert-content">
				<strong><?php esc_html_e( 'No diagnostics found', 'wpshadow' ); ?></strong>
				<p class="wps-no-results-copy">
					<?php esc_html_e( 'Try adjusting your filters to see results.', 'wpshadow' ); ?>
				</p>
			</div>
		</div>

	</div>

	<?php
}


/* ============================================================
   DIAGNOSTIC DETAIL PAGE
   ============================================================ */

/**
 * Render the detail page for a single diagnostic.
 *
 * @since 0.6093.1200
 */
function wpshadow_render_diagnostic_detail_v2() {
	$selected_run_key = Form_Param_Helper::get( 'diagnostic', 'key', '' );
	$back_url         = admin_url( 'admin.php?page=wpshadow-guardian' );

	if ( '' === $selected_run_key ) {
		?>
		<div class="wps-page-container">
			<div class="wps-alert wps-alert--danger">
				<div class="wps-alert-icon">✕</div>
				<div class="wps-alert-content">
					<?php esc_html_e( 'No diagnostic was selected.', 'wpshadow' ); ?>
				</div>
			</div>
			<a href="<?php echo esc_url( $back_url ); ?>" class="wps-button wps-button--secondary wps-back-link wps-back-link--top">
				<?php esc_html_e( '← Back to Guardian', 'wpshadow' ); ?>
			</a>
		</div>
		<?php
		return;
	}

	$rows     = wpshadow_get_diagnostics_activity_rows();
	$selected = null;

	foreach ( $rows as $row ) {
		if ( isset( $row['run_key'] ) && $selected_run_key === (string) $row['run_key'] ) {
			$selected = $row;
			break;
		}
	}

	if ( ! is_array( $selected ) ) {
		?>
		<div class="wps-page-container">
			<div class="wps-alert wps-alert--danger">
				<div class="wps-alert-icon">✕</div>
				<div class="wps-alert-content">
					<?php esc_html_e( 'Diagnostic not found.', 'wpshadow' ); ?>
				</div>
			</div>
			<a href="<?php echo esc_url( $back_url ); ?>" class="wps-button wps-button--secondary wps-back-link wps-back-link--top">
				<?php esc_html_e( '← Back to Guardian', 'wpshadow' ); ?>
			</a>
		</div>
		<?php
		return;
	}

	$name           = isset( $selected['name'] ) ? (string) $selected['name'] : '';
	$description    = isset( $selected['description'] ) ? (string) $selected['description'] : '';
	$class_name     = isset( $selected['class'] ) ? (string) $selected['class'] : '';
	$status_raw     = isset( $selected['status_raw'] ) ? (string) $selected['status_raw'] : 'unknown';
	$status         = isset( $selected['status'] ) ? (string) $selected['status'] : '';
	$confidence     = isset( $selected['confidence'] ) ? (string) $selected['confidence'] : 'standard';
	$is_core        = ! empty( $selected['is_core'] );
	$is_enabled     = ! empty( $selected['enabled'] );
	$last_run       = isset( $selected['last_run'] ) ? (string) $selected['last_run'] : '';
	$next_run       = isset( $selected['next_run'] ) ? (string) $selected['next_run'] : '';
	$failure_reason = isset( $selected['failure_reason'] ) ? (string) $selected['failure_reason'] : '';
	$failure_issues = isset( $selected['failure_issues'] ) && is_array( $selected['failure_issues'] )
		? $selected['failure_issues'] : array();
	$explanation_sections = isset( $selected['explanation_sections'] ) && is_array( $selected['explanation_sections'] )
		? $selected['explanation_sections']
		: array();
	$summary_text = isset( $explanation_sections['summary'] ) ? trim( (string) $explanation_sections['summary'] ) : '';
	$how_tested_text = isset( $explanation_sections['how_wp_shadow_tested'] ) ? trim( (string) $explanation_sections['how_wp_shadow_tested'] ) : '';
	$why_matters_text = isset( $explanation_sections['why_it_matters'] ) ? trim( (string) $explanation_sections['why_it_matters'] ) : '';
	$how_to_fix_text = isset( $explanation_sections['how_to_fix_it'] ) ? trim( (string) $explanation_sections['how_to_fix_it'] ) : '';

	$toggle_nonce = wp_create_nonce( 'wpshadow_scan_settings' );
	$autofix_nonce = wp_create_nonce( 'wpshadow_autofix' );
	$treatment_inputs_nonce = wp_create_nonce( 'wpshadow_treatment_inputs' );
	$treatment_input_requirements = array();
	$treatment_input_values       = array();
	if ( class_exists( '\\WPShadow\\Core\\Treatment_Input_Requirements' ) ) {
		$treatment_input_requirements = \WPShadow\Core\Treatment_Input_Requirements::get_for_finding( $selected_run_key );
		$treatment_input_values       = \WPShadow\Core\Treatment_Input_Requirements::get_saved_values( $selected_run_key );
	}
	$manual_fix_reason = function_exists( 'wpshadow_get_automation_constraint_reason' )
		? wpshadow_get_automation_constraint_reason( $selected_run_key, $name, $description, (string) ( $selected['family'] ?? '' ), $failure_reason )
		: '';

	// Frequency override.
	$freq_overrides = get_option( 'wpshadow_diagnostic_frequency_overrides', array() );
	$freq_overrides = is_array( $freq_overrides ) ? $freq_overrides : array();
	$frequency_labels = array(
		'always'    => __( 'Every request', 'wpshadow' ),
		'on-change' => __( 'On change', 'wpshadow' ),
		'daily'     => __( 'Daily', 'wpshadow' ),
		'weekly'    => __( 'Weekly', 'wpshadow' ),
		'monthly'   => __( 'Monthly', 'wpshadow' ),
		'disabled'  => __( 'Disabled', 'wpshadow' ),
	);
	$default_frequency = 'daily';
	if ( class_exists( '\\WPShadow\\Core\\Diagnostic_Metadata' ) ) {
		$default_meta = \WPShadow\Core\Diagnostic_Metadata::get( $selected_run_key );
		if ( ! empty( $default_meta['scan_frequency'] ) ) {
			$default_frequency = (string) $default_meta['scan_frequency'];
		}
	}
	if ( 'daily' === $default_frequency && '' !== $class_name && class_exists( $class_name ) && method_exists( $class_name, 'get_scan_frequency' ) ) {
		$default_frequency = (string) call_user_func( array( $class_name, 'get_scan_frequency' ) );
	}
	if ( ! isset( $frequency_labels[ $default_frequency ] ) ) {
		$default_frequency = 'daily';
	}
	$frequency_str = ! $is_enabled
		? 'disabled'
		: ( isset( $freq_overrides[ $class_name ] ) ? (string) $freq_overrides[ $class_name ] : 'default' );
	if ( 'default' !== $frequency_str && ! isset( $frequency_labels[ $frequency_str ] ) ) {
		$frequency_str = 'default';
	}
	$is_scheduled_disabled   = 'disabled' === $frequency_str;
	$default_frequency_label = $frequency_labels[ $default_frequency ];
	$current_frequency_label = 'default' === $frequency_str
		? sprintf(
			/* translators: %s: default schedule label */
			__( 'Default (%s)', 'wpshadow' ),
			$default_frequency_label
		)
		: ( $frequency_labels[ $frequency_str ] ?? ucfirst( str_replace( '-', ' ', $frequency_str ) ) );
	$schedule_help_text = sprintf(
		/* translators: 1: default schedule, 2: current schedule */
		__( 'Default for this diagnostic: %1$s. Current setting: %2$s. Select Disabled to stop future runs.', 'wpshadow' ),
		$default_frequency_label,
		$current_frequency_label
	);
	$freq_nonce = wp_create_nonce( 'wpshadow_scan_settings' );

	// Treatment auditability: show whether an automated fix exists for this diagnostic.
	$tx_class    = null;
	$tx_maturity = null;
	$tx_risk     = null;
	$tx_enabled  = false;
	$tx_default_enabled = false;
	$tx_is_file_write = false;
	$tx_file_review_url = '';
	$tx_change_summary = '';
	if ( class_exists( '\WPShadow\Treatments\Treatment_Registry' ) ) {
		$tx_class = \WPShadow\Treatments\Treatment_Registry::get_treatment( $selected_run_key );
		if ( null !== $tx_class && class_exists( '\WPShadow\Core\Treatment_Metadata' ) ) {
			$tx_finding_id = method_exists( $tx_class, 'get_finding_id' )
				? sanitize_key( (string) $tx_class::get_finding_id() )
				: $selected_run_key;
			$tx_meta       = \WPShadow\Core\Treatment_Metadata::get( $tx_finding_id );
			$tx_maturity   = isset( $tx_meta['maturity'] ) ? (string) $tx_meta['maturity'] : null;
			$tx_risk       = isset( $tx_meta['risk_level'] ) ? (string) $tx_meta['risk_level'] : null;

			if ( method_exists( $tx_class, 'get_proposed_change_summary' ) ) {
				$tx_change_summary = trim( (string) $tx_class::get_proposed_change_summary() );
			}

			if ( '' === $tx_change_summary ) {
				$summary_map = array(
					'auto-update-policy'                => __( 'Re-enables WordPress core auto-updates when they were disabled by option, while refusing to override a deliberate wp-config constant.', 'wpshadow' ),
					'fatal-error-handler-enabled'       => __( 'Comments out the setting that disables WordPress recovery mode so fatal plugin or theme errors can trigger the built-in recovery safety net again.', 'wpshadow' ),
					'discussion-defaults'                => __( 'Applies a conservative anti-spam baseline for new content by closing comments and pings by default and turning moderation back on.', 'wpshadow' ),
					'homepage-page-published'            => __( 'Publishes the page assigned as the static homepage, or creates a simple Home page if no usable page is currently selected.', 'wpshadow' ),
					'legal-pages-linked-footer'          => __( 'Creates or updates a footer-style navigation path so the privacy policy is reachable from a legal or utility menu area.', 'wpshadow' ),
					'media-year-month-folders-enabled'   => __( 'Turns on year/month upload folders so new media files are organized into dated subdirectories instead of one flat uploads directory.', 'wpshadow' ),
					'permalink-structure-meaningful'     => __( 'Switches the site to a readable /%postname%/ permalink structure and refreshes rewrite rules so URLs are more meaningful for users and search engines.', 'wpshadow' ),
					'posts-page-published'               => __( 'Publishes the page assigned as the Posts Page, or creates a placeholder Blog page when the configured page is missing.', 'wpshadow' ),
					'posts-per-page-optimized'           => __( 'Resets the posts-per-page setting to a balanced default so archive pages stay performant without hiding too much content.', 'wpshadow' ),
					'privacy-policy-links-visible'       => __( 'Ensures a published privacy policy exists and adds it to navigation so visitors can reach it easily from the site menu structure.', 'wpshadow' ),
					'registration-setting-intentional'   => __( 'Disables public user self-registration so visitors cannot create accounts unless that flow is intentionally enabled for the site.', 'wpshadow' ),
					'rss-version-leak'                    => __( 'Removes the WordPress version marker from RSS output so your exact core version is not advertised publicly in feed metadata.', 'wpshadow' ),
					'rss-feed-summary'                    => __( 'Changes feeds to publish post summaries instead of full article bodies, reducing content scraping and duplicate-content risk.', 'wpshadow' ),
					'script-debug-production'            => __( 'Comments out a truthy SCRIPT_DEBUG define so WordPress stops serving unminified development builds of core JavaScript and CSS on the live site.', 'wpshadow' ),
					'rss-head-links'                      => __( 'Removes RSS feed autodiscovery link tags from your page head so head output is leaner and feed endpoint exposure is reduced.', 'wpshadow' ),
					'pingback-head-link'                  => __( 'Removes the pingback link tag from your page head and suppresses the X-Pingback HTTP header so your xmlrpc.php URL is not advertised on every page.', 'wpshadow' ),
					'wlwmanifest-link'                    => __( 'Removes the legacy WLW manifest header tag from your page head to reduce unnecessary public endpoint disclosure.', 'wpshadow' ),
					'rsd-link'                            => __( 'Removes the Really Simple Discovery (RSD) link from your page head to reduce exposure of legacy remote publishing interfaces.', 'wpshadow' ),
					'shortlink-head-tag'                  => __( 'Removes the shortlink tag from your page head to reduce unneeded metadata and keep head output leaner.', 'wpshadow' ),
					'adjacent-posts-links'                => __( 'Removes adjacent post relation links from your page head to reduce unnecessary metadata output.', 'wpshadow' ),
					'emoji-assets'                        => __( 'Disables front-end emoji scripts/styles so browsers skip loading extra assets that are often unnecessary.', 'wpshadow' ),
					'oembed-discovery-links'              => __( 'Removes oEmbed discovery links from page head when not required, reducing public metadata and head bloat.', 'wpshadow' ),
					'rest-api-head-link'                  => __( 'Removes REST API discovery link tags from page head while keeping API behavior itself unchanged.', 'wpshadow' ),
					'comments-auto-close-old-posts'       => __( 'Enables automatic comment closing on older posts to reduce spam exposure on stale content.', 'wpshadow' ),
					'comment-moderation-enabled'          => __( 'Enables moderation for new comments so submissions require review before becoming publicly visible.', 'wpshadow' ),
					'pingbacks-trackbacks'                => __( 'Disables pingbacks/trackbacks for new posts to reduce link-spam and abuse vectors.', 'wpshadow' ),
					'page-cache-enabled'                  => __( 'Applies cache-related settings so public pages can be served faster with reduced PHP/database load.', 'wpshadow' ),
					'expired-transients-cleared'          => __( 'Cleans expired temporary data from wp_options to reduce unnecessary table bloat.', 'wpshadow' ),
					'search-engine-visibility-intentional' => __( 'Turns search engine visibility back on so WordPress stops discouraging indexing of the live site.', 'wpshadow' ),
					'admin-account-count-minimized'       => __( 'Guides reduction of unnecessary administrator access so fewer accounts have full-site control.', 'wpshadow' ),
					'update-services-intentional'         => __( 'Clears the legacy Update Services list so low-publishing sites stop pinging third-party blog aggregators on every publish.', 'wpshadow' ),
				);

				$tx_change_summary = $summary_map[ $tx_finding_id ] ?? __( 'Applies a targeted configuration change for this diagnostic and keeps the change reversible where supported.', 'wpshadow' );
			}
		}

		$disabled_treatments = get_option( 'wpshadow_disabled_treatment_classes', array() );
		if ( ! is_array( $disabled_treatments ) ) {
			$disabled_treatments = array();
		}

		if ( is_string( $tx_class ) && '' !== $tx_class ) {
			$tx_enabled = ! in_array( $tx_class, $disabled_treatments, true );
			$tx_enabled = (bool) apply_filters( 'wpshadow_treatment_enabled', $tx_enabled, $tx_class );

			if ( class_exists( '\WPShadow\Admin\File_Write_Registry' ) ) {
				$tx_file_write_info = \WPShadow\Admin\File_Write_Registry::get_treatment_info( $tx_class );
				if ( is_array( $tx_file_write_info ) ) {
					$tx_is_file_write   = true;
					$tx_file_review_url = admin_url( 'admin.php?page=wpshadow-file-review#wpshadow-review-card-' . rawurlencode( $selected_run_key ) );
				}
			}

			if ( class_exists( '\WPShadow\Core\Treatment_Toggle_Policy' ) ) {
				$tx_default_enabled = \WPShadow\Core\Treatment_Toggle_Policy::is_default_enabled_for_class( $tx_class );
			} else {
				$tx_default_enabled = ( 'shipped' === $tx_maturity && 'safe' === $tx_risk );
			}
		}
	}
	?>

	<div class="wps-page-container">

		<!-- Back Button -->
		<a href="<?php echo esc_url( $back_url ); ?>" class="wps-button wps-button--secondary wps-back-link">
			<?php esc_html_e( '← Back to Guardian', 'wpshadow' ); ?>
		</a>

		<!-- Header -->
		<div class="wps-page-header">
			<div class="wps-page-header-content">
				<h1><?php echo esc_html( $name ); ?></h1>
				<div class="wps-detail-badges wps-mt-3">
					<?php if ( $is_core ) : ?>
						<span class="wps-badge wps-badge--core"><?php esc_html_e( 'Core Check', 'wpshadow' ); ?></span>
					<?php endif; ?>
					<span class="wps-badge wps-badge--confidence-<?php echo esc_attr( $confidence ); ?>">
						<?php echo esc_html( ucfirst( $confidence ) . ' ' . __( 'Confidence', 'wpshadow' ) ); ?>
					</span>
					<?php if ( 'passed' === $status_raw ) : ?>
						<span class="wps-badge wps-badge--pass"><?php esc_html_e( 'Passed', 'wpshadow' ); ?></span>
					<?php elseif ( 'failed' === $status_raw ) : ?>
						<span class="wps-badge wps-badge--fail"><?php esc_html_e( 'Issue Found', 'wpshadow' ); ?></span>
					<?php else : ?>
						<span class="wps-badge"><?php echo esc_html( $status ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Two-Column Layout -->
		<div class="wps-layout-sidebar">

			<!-- Main Content -->
			<div class="wps-layout-sidebar-main">

				<div class="wps-card">
					<div class="wps-card-header">
						<h2 class="wps-card-title"><?php esc_html_e( 'About This Check', 'wpshadow' ); ?></h2>
					</div>
					<div class="wps-card-body">
						<?php if ( '' !== $summary_text ) : ?>
							<p><?php echo esc_html( $summary_text ); ?></p>
						<?php elseif ( '' !== $description ) : ?>
							<p><?php echo esc_html( $description ); ?></p>
						<?php endif; ?>
						<?php if ( '' !== $how_tested_text ) : ?>
							<p><?php echo esc_html( $how_tested_text ); ?></p>
						<?php endif; ?>
						<?php if ( '' !== $why_matters_text ) : ?>
							<p><?php echo esc_html( $why_matters_text ); ?></p>
						<?php endif; ?>
						<?php if ( 'failed' === $status_raw && '' !== $failure_reason ) : ?>
							<div class="wps-alert wps-alert--danger wps-callout">
								<div class="wps-alert-icon">✕</div>
								<div class="wps-alert-content">
									<strong><?php esc_html_e( 'Issue Detected', 'wpshadow' ); ?></strong>
									<p class="wps-callout-copy">
										<?php echo esc_html( $failure_reason ); ?>
									</p>
								</div>
							</div>
						<?php endif; ?>
						<?php if ( ! empty( $failure_issues ) ) : ?>
							<div class="wps-detail-list-wrap">
								<strong><?php esc_html_e( 'Issues Found:', 'wpshadow' ); ?></strong>
								<ul class="wps-detail-list">
									<?php foreach ( $failure_issues as $issue ) : ?>
										<li class="wps-detail-list-item">
											<?php echo esc_html( trim( wp_strip_all_tags( $issue ), '- ' ) ); ?>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title"><?php esc_html_e( 'Fix Available', 'wpshadow' ); ?></h3>
					</div>
					<div class="wps-card-body">
						<?php
						$requirement_fields = isset( $treatment_input_requirements['fields'] ) && is_array( $treatment_input_requirements['fields'] )
							? $treatment_input_requirements['fields']
							: array();
						?>
						<?php if ( null === $tx_class ) : ?>
							<?php if ( ! empty( $requirement_fields ) ) : ?>
								<p class="wps-action-note">
									<?php esc_html_e( 'This fix needs a small amount of input from you. Use the form below and WPShadow will save the values and update the matching setting immediately where supported.', 'wpshadow' ); ?>
								</p>
								<?php if ( '' !== $manual_fix_reason ) : ?>
									<p class="wps-action-note">
										<strong><?php esc_html_e( 'Why WPShadow is asking you first:', 'wpshadow' ); ?></strong>
										<?php echo ' ' . esc_html( $manual_fix_reason ); ?>
									</p>
								<?php endif; ?>
							<?php else : ?>
								<p class="wps-action-note">
									<?php esc_html_e( 'No automated fix available. Manual resolution required.', 'wpshadow' ); ?>
								</p>
								<?php if ( '' !== $manual_fix_reason ) : ?>
									<p class="wps-action-note">
										<strong><?php esc_html_e( 'Why WPShadow is not auto-fixing this:', 'wpshadow' ); ?></strong>
										<?php echo ' ' . esc_html( $manual_fix_reason ); ?>
									</p>
								<?php endif; ?>
							<?php endif; ?>
						<?php elseif ( 'shipped' === $tx_maturity ) : ?>
							<div class="wps-action-stack">
								<p class="wps-action-note">
									<strong><?php esc_html_e( 'What this fix does:', 'wpshadow' ); ?></strong>
									<?php echo ' ' . esc_html( $tx_change_summary ); ?>
								</p>
								<div class="wps-attention-inline">
									<span class="wps-badge wps-badge--pass">✓ <?php esc_html_e( 'Automated', 'wpshadow' ); ?></span>
									<?php if ( $tx_risk ) : ?>
										<span class="wps-badge wps-badge--confidence-<?php echo esc_attr( $tx_risk ); ?>">
											<?php echo esc_html( ucfirst( $tx_risk ) . ' ' . __( 'Risk', 'wpshadow' ) ); ?>
										</span>
									<?php endif; ?>
								</div>
								<?php if ( 'failed' === $status_raw ) : ?>
									<?php if ( $tx_is_file_write ) : ?>
										<p class="wps-action-note">
											<?php esc_html_e( 'This fix changes a file on disk, so WPShadow sends you through the review page before applying it.', 'wpshadow' ); ?>
										</p>
										<p class="wps-alert-action">
											<a href="<?php echo esc_url( $tx_file_review_url ); ?>" class="wps-button wps-button--primary">
												<?php esc_html_e( 'Run Fix', 'wpshadow' ); ?>
											</a>
										</p>
									<?php else : ?>
										<p class="wps-action-note">
											<?php esc_html_e( 'WPShadow can apply this fix right now from Guardian.', 'wpshadow' ); ?>
										</p>
										<p class="wps-alert-action">
											<button
												type="button"
												id="wps-run-treatment"
												class="wps-button wps-button--primary"
												data-finding-id="<?php echo esc_attr( $selected_run_key ); ?>"
												data-nonce="<?php echo esc_attr( $autofix_nonce ); ?>"
											>
												<?php esc_html_e( 'Run Fix', 'wpshadow' ); ?>
											</button>
										</p>
										<div id="wps-run-treatment-status" class="wps-status-message"></div>
									<?php endif; ?>
								<?php else : ?>
									<p class="wps-action-note">
										<?php esc_html_e( 'This fix is ready and can be applied the next time the issue is detected.', 'wpshadow' ); ?>
									</p>
								<?php endif; ?>
								<div class="wps-inline-toggle">
									<label class="wps-toggle-switch" for="wps-treatment-auto-toggle">
										<input
											type="checkbox"
											id="wps-treatment-auto-toggle"
											data-class-name="<?php echo esc_attr( $tx_class ); ?>"
											data-nonce="<?php echo esc_attr( $toggle_nonce ); ?>"
											<?php checked( $tx_enabled ); ?>
										/>
										<span class="wps-toggle-slider" aria-hidden="true"></span>
									</label>
									<label for="wps-treatment-auto-toggle" class="wps-inline-toggle-label">
										<?php esc_html_e( 'Automatically apply this fix when WPShadow detects the issue again', 'wpshadow' ); ?>
									</label>
								</div>
								<?php if ( $tx_default_enabled ) : ?>
									<p class="wps-action-note">
										<?php esc_html_e( 'Default policy: ON, because this treatment is classified as shipped + safe.', 'wpshadow' ); ?>
									</p>
								<?php else : ?>
									<p class="wps-action-note">
										<?php esc_html_e( 'Default policy: OFF, because this treatment is not classified as safe for unattended application.', 'wpshadow' ); ?>
									</p>
								<?php endif; ?>
								<div id="wps-treatment-toggle-status" class="wps-status-message"></div>
							</div>
						<?php else : ?>
							<div class="wps-action-stack">
								<p class="wps-action-note">
									<strong><?php esc_html_e( 'What this fix does:', 'wpshadow' ); ?></strong>
									<?php echo ' ' . esc_html( $tx_change_summary ); ?>
								</p>
								<span class="wps-badge wps-badge--guidance">
									<?php esc_html_e( 'Guidance Only', 'wpshadow' ); ?>
								</span>
								<p class="wps-action-note">
									<?php esc_html_e( 'Step-by-step instructions are provided. No automated change is made.', 'wpshadow' ); ?>
								</p>
								<?php if ( '' !== $manual_fix_reason ) : ?>
									<p class="wps-action-note">
										<strong><?php esc_html_e( 'Why WPShadow is not auto-fixing this:', 'wpshadow' ); ?></strong>
										<?php echo ' ' . esc_html( $manual_fix_reason ); ?>
									</p>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $requirement_fields ) ) : ?>
							<hr />
							<div class="wps-action-stack" id="wps-treatment-inputs-form" data-finding-id="<?php echo esc_attr( $selected_run_key ); ?>" data-nonce="<?php echo esc_attr( $treatment_inputs_nonce ); ?>">
								<p class="wps-action-note">
									<strong>
										<?php echo esc_html( isset( $treatment_input_requirements['title'] ) ? (string) $treatment_input_requirements['title'] : __( 'Before You Run This Fix', 'wpshadow' ) ); ?>
									</strong>
								</p>
								<?php foreach ( $requirement_fields as $field ) : ?>
									<?php
									$field_key        = isset( $field['key'] ) ? sanitize_key( (string) $field['key'] ) : '';
									$field_type       = isset( $field['type'] ) ? (string) $field['type'] : 'text';
									$field_label      = isset( $field['label'] ) ? (string) $field['label'] : '';
									$field_help       = isset( $field['description'] ) ? (string) $field['description'] : '';
									$field_why        = isset( $field['why'] ) ? (string) $field['why'] : '';
									$field_manual     = isset( $field['manual'] ) ? (string) $field['manual'] : '';
									$field_placeholder = isset( $field['placeholder'] ) ? (string) $field['placeholder'] : '';
									$field_options    = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
									$field_required   = ! empty( $field['required'] );
									$field_value      = isset( $treatment_input_values[ $field_key ] ) ? (string) $treatment_input_values[ $field_key ] : '';
									$field_id         = 'wps-treatment-input-' . $field_key;
									$field_list_id    = $field_id . '-list';
									?>
									<?php if ( '' === $field_key || '' === $field_label ) : ?>
										<?php continue; ?>
									<?php endif; ?>
									<div class="wps-action-note">
										<?php if ( 'toggle' === $field_type ) : ?>
											<div class="wps-inline-toggle">
												<label class="wps-toggle-switch" for="<?php echo esc_attr( $field_id ); ?>">
													<input
														type="checkbox"
														id="<?php echo esc_attr( $field_id ); ?>"
														data-input-key="<?php echo esc_attr( $field_key ); ?>"
														data-input-type="toggle"
														data-required="<?php echo $field_required ? '1' : '0'; ?>"
														<?php checked( '1' === $field_value ); ?>
													/>
													<span class="wps-toggle-slider" aria-hidden="true"></span>
												</label>
												<label for="<?php echo esc_attr( $field_id ); ?>" class="wps-inline-toggle-label">
													<?php echo esc_html( $field_label ); ?>
												</label>
											</div>
										<?php elseif ( 'select' === $field_type ) : ?>
											<label class="wps-field" for="<?php echo esc_attr( $field_id ); ?>">
												<span class="wps-field-label"><?php echo esc_html( $field_label ); ?></span>
												<select
													id="<?php echo esc_attr( $field_id ); ?>"
													class="wps-w-full"
													data-input-key="<?php echo esc_attr( $field_key ); ?>"
													data-input-type="text"
													data-required="<?php echo $field_required ? '1' : '0'; ?>"
												>
													<option value=""><?php esc_html_e( 'Select an option', 'wpshadow' ); ?></option>
													<?php foreach ( $field_options as $option ) : ?>
														<?php
														$option_value = isset( $option['value'] ) ? (string) $option['value'] : '';
														$option_label = isset( $option['label'] ) ? (string) $option['label'] : $option_value;
														if ( '' === $option_value ) {
															continue;
														}
														?>
														<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $field_value, $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
													<?php endforeach; ?>
												</select>
											</label>
										<?php elseif ( 'datalist' === $field_type ) : ?>
											<label class="wps-field" for="<?php echo esc_attr( $field_id ); ?>">
												<span class="wps-field-label"><?php echo esc_html( $field_label ); ?></span>
												<input
													type="text"
													id="<?php echo esc_attr( $field_id ); ?>"
													class="wps-w-full"
													placeholder="<?php echo esc_attr( $field_placeholder ); ?>"
													list="<?php echo esc_attr( $field_list_id ); ?>"
													data-input-key="<?php echo esc_attr( $field_key ); ?>"
													data-input-type="text"
													data-required="<?php echo $field_required ? '1' : '0'; ?>"
													value="<?php echo esc_attr( $field_value ); ?>"
												/>
												<datalist id="<?php echo esc_attr( $field_list_id ); ?>">
													<?php foreach ( $field_options as $option ) : ?>
														<?php
														$option_value = isset( $option['value'] ) ? (string) $option['value'] : '';
														$option_label = isset( $option['label'] ) ? (string) $option['label'] : '';
														if ( '' === $option_value ) {
															continue;
														}
														?>
														<option value="<?php echo esc_attr( $option_value ); ?>" label="<?php echo esc_attr( $option_label ); ?>"></option>
													<?php endforeach; ?>
												</datalist>
											</label>
										<?php else : ?>
											<label class="wps-field" for="<?php echo esc_attr( $field_id ); ?>">
												<span class="wps-field-label"><?php echo esc_html( $field_label ); ?></span>
												<input
													type="text"
													id="<?php echo esc_attr( $field_id ); ?>"
													class="wps-w-full"
													placeholder="<?php echo esc_attr( $field_placeholder ); ?>"
													data-input-key="<?php echo esc_attr( $field_key ); ?>"
													data-input-type="text"
													data-required="<?php echo $field_required ? '1' : '0'; ?>"
													value="<?php echo esc_attr( $field_value ); ?>"
												/>
											</label>
										<?php endif; ?>
										<?php if ( '' !== $field_help ) : ?>
											<p class="wps-field-help"><?php echo esc_html( $field_help ); ?></p>
										<?php endif; ?>
										<?php if ( '' !== $field_why ) : ?>
											<p class="wps-action-note">
												<strong><?php esc_html_e( 'Why this matters:', 'wpshadow' ); ?></strong>
												<?php echo ' ' . esc_html( $field_why ); ?>
											</p>
										<?php endif; ?>
										<?php if ( '' !== $field_manual ) : ?>
											<div class="wps-alert wps-alert--info wps-callout">
												<div class="wps-alert-icon">i</div>
												<div class="wps-alert-content">
													<strong><?php esc_html_e( 'Manual option', 'wpshadow' ); ?></strong>
													<p class="wps-callout-copy"><?php echo esc_html( $field_manual ); ?></p>
												</div>
											</div>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
								<button id="wps-save-treatment-inputs" class="wps-button wps-button--secondary wps-w-full">
									<?php esc_html_e( 'Save Fix Inputs', 'wpshadow' ); ?>
								</button>
								<div id="wps-treatment-inputs-status" class="wps-status-message"></div>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( 'failed' === $status_raw ) : ?>
				<div class="wps-card">
					<div class="wps-card-header">
						<h2 class="wps-card-title"><?php esc_html_e( 'How to Address This Manually', 'wpshadow' ); ?></h2>
					</div>
					<div class="wps-card-body">
						<?php if ( '' !== $how_to_fix_text ) : ?>
							<p><?php echo esc_html( $how_to_fix_text ); ?></p>
						<?php endif; ?>
						<ol class="wps-detail-steps">
							<li>
								<?php esc_html_e( 'Open the relevant WordPress setting or plugin screen and apply the change described above manually.', 'wpshadow' ); ?>
							</li>
							<li>
								<?php esc_html_e( 'Save changes, then clear any cache layer (plugin/CDN/server cache) so the update is fully applied.', 'wpshadow' ); ?>
							</li>
							<li>
								<?php esc_html_e( 'Wait for the next scheduled run to verify the result changes to Passed.', 'wpshadow' ); ?>
							</li>
						</ol>
						<div class="wps-alert wps-alert--info wps-callout">
							<div class="wps-alert-icon">💡</div>
							<div class="wps-alert-content">
								<?php esc_html_e( 'Most issues have multiple solution paths — choose the one that fits your setup.', 'wpshadow' ); ?>
							</div>
						</div>
					</div>
				</div>
				<?php endif; ?>

			</div>

			<!-- Sidebar -->
			<div class="wps-layout-sidebar-aside">

				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title"><?php esc_html_e( 'Status', 'wpshadow' ); ?></h3>
					</div>
					<div class="wps-card-body">
						<dl class="wps-detail-status-list">
							<div>
								<dt class="wps-detail-status-term"><?php esc_html_e( 'Result', 'wpshadow' ); ?></dt>
								<dd class="wps-detail-status-def"><?php echo esc_html( $status ); ?></dd>
							</div>
							<div>
								<dt class="wps-detail-status-term"><?php esc_html_e( 'Last Run', 'wpshadow' ); ?></dt>
								<dd class="wps-detail-status-def"><?php echo wp_kses_post( $last_run ); ?></dd>
							</div>
							<div>
								<dt class="wps-detail-status-term"><?php esc_html_e( 'Next Run', 'wpshadow' ); ?></dt>
								<dd class="wps-detail-status-def"><?php echo wp_kses_post( $next_run ); ?></dd>
							</div>
							<div>
								<dt class="wps-detail-status-term"><?php esc_html_e( 'Schedule', 'wpshadow' ); ?></dt>
								<dd class="wps-detail-status-def"><?php echo esc_html( $current_frequency_label ); ?></dd>
							</div>
						</dl>
					</div>
				</div>

				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title"><?php esc_html_e( 'Schedule', 'wpshadow' ); ?></h3>
					</div>
					<div class="wps-card-body">
						<label class="wps-field">
							<span class="wps-field-label"><?php esc_html_e( 'Run Frequency', 'wpshadow' ); ?></span>
							<select id="wps-frequency-select" data-class-name="<?php echo esc_attr( $class_name ); ?>" data-nonce="<?php echo esc_attr( $freq_nonce ); ?>" class="wps-w-full">
								<option value="default"   <?php selected( $frequency_str, 'default' ); ?>><?php echo esc_html( sprintf( __( 'Default (%s)', 'wpshadow' ), $default_frequency_label ) ); ?></option>
								<option value="always"    <?php selected( $frequency_str, 'always' ); ?>><?php esc_html_e( 'Every request', 'wpshadow' ); ?></option>
								<option value="on-change" <?php selected( $frequency_str, 'on-change' ); ?>><?php esc_html_e( 'On change', 'wpshadow' ); ?></option>
								<option value="daily"     <?php selected( $frequency_str, 'daily' ); ?>><?php esc_html_e( 'Daily', 'wpshadow' ); ?></option>
								<option value="weekly"    <?php selected( $frequency_str, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'wpshadow' ); ?></option>
								<option value="monthly"   <?php selected( $frequency_str, 'monthly' ); ?>><?php esc_html_e( 'Monthly', 'wpshadow' ); ?></option>
								<option value="disabled"  <?php selected( $frequency_str, 'disabled' ); ?>><?php esc_html_e( 'Disabled', 'wpshadow' ); ?></option>
							</select>
							<p class="wps-field-help">
								<?php echo esc_html( $schedule_help_text ); ?>
							</p>
						</label>
						<button id="wps-save-frequency" class="wps-button wps-button--secondary wps-w-full">
							<?php esc_html_e( 'Save', 'wpshadow' ); ?>
						</button>
						<div id="wps-frequency-status" data-status-message class="wps-status-message"></div>
					</div>
				</div>
			</div>

		</div>

	</div>

	<?php
}
