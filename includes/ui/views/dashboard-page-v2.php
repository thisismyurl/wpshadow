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
	$score_color = $score >= 80
		? 'var(--wps-status-pass)'
		: ( $score >= 60 ? 'var(--wps-amber-500)' : 'var(--wps-status-fail)' );

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

	$run_nonce    = wp_create_nonce( 'wpshadow_scan_nonce' );
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
			<div class="wps-page-actions" style="margin-left: auto; margin-bottom: 0;">
				<button
					id="wps-run-all"
					class="wps-button wps-button--primary"
					data-nonce="<?php echo esc_attr( $run_nonce ); ?>"
				>
					<?php esc_html_e( 'Run All Tests', 'wpshadow' ); ?>
				</button>
				<a href="<?php echo esc_url( $guardian_url ); ?>" class="wps-button wps-button--secondary">
					<?php esc_html_e( 'Open Guardian →', 'wpshadow' ); ?>
				</a>
			</div>
		</div>

		<!-- Run-All Status Bar (hidden until running) -->
		<div id="wps-run-all-status" style="display:none; padding: var(--wps-space-4); background: var(--wps-gray-100); border-radius: var(--wps-radius-md); margin-bottom: var(--wps-space-8); font-size: var(--wps-text-sm); color: var(--wps-gray-700);"></div>

		<!-- Stats Row -->
		<div class="wps-grid wps-grid--3col" style="margin-bottom: var(--wps-space-8);">
			<div class="wps-stat">
				<div class="wps-stat-value" style="color: <?php echo esc_attr( $score_color ); ?>;"><?php echo esc_html( $score ); ?>%</div>
				<div class="wps-stat-label"><?php esc_html_e( 'Health Score', 'wpshadow' ); ?></div>
			</div>
			<div class="wps-stat">
				<div class="wps-stat-value" style="color: var(--wps-status-pass);"><?php echo esc_html( $passed ); ?></div>
				<div class="wps-stat-label"><?php esc_html_e( 'Checks Passed', 'wpshadow' ); ?></div>
			</div>
			<div class="wps-stat">
				<div class="wps-stat-value" style="color: <?php echo $failed > 0 ? 'var(--wps-status-fail)' : 'var(--wps-gray-400)'; ?>;"><?php echo esc_html( $failed ); ?></div>
				<div class="wps-stat-label"><?php esc_html_e( 'Issues Found', 'wpshadow' ); ?></div>
			</div>
		</div>

		<?php if ( $pending > 0 ) : ?>
		<div class="wps-alert wps-alert--warning" style="margin-bottom: var(--wps-space-8);">
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
				<p style="margin: var(--wps-space-2) 0 0; font-size: var(--wps-text-sm);">
					<?php esc_html_e( 'WPShadow only shows a final all-clear after every enabled test has reported a real result. Run all tests to finish the scan and refresh this dashboard.', 'wpshadow' ); ?>
				</p>
				<p style="margin: var(--wps-space-3) 0 0;">
					<button
						type="button"
						class="wps-button wps-button--primary"
						onclick="var btn=document.getElementById('wps-run-all'); if (btn) { btn.click(); }"
					>
						<?php esc_html_e( 'Run All Tests', 'wpshadow' ); ?>
					</button>
				</p>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $top_issues ) ) : ?>
		<!-- Attention Needed -->
		<div class="wps-card" style="margin-bottom: var(--wps-space-8); border-left: 4px solid var(--wps-status-fail);">
			<div class="wps-card-header">
				<h2 class="wps-card-title" style="color: var(--wps-status-fail);">
					<?php
					printf(
						/* translators: %d: number of issues */
						esc_html( _n( '⚠️ %d Issue Needs Attention', '⚠️ %d Issues Need Attention', count( $failing ), 'wpshadow' ) ),
						(int) count( $failing )
					);
					?>
				</h2>
				<p style="margin: var(--wps-space-2) 0 0; font-size: var(--wps-text-sm); color: var(--wps-gray-600);">
					<?php esc_html_e( 'Core checks are listed first — they have the highest confidence and impact.', 'wpshadow' ); ?>
				</p>
			</div>
			<div class="wps-card-body" style="padding-top: 0;">
				<table style="width: 100%; border-collapse: collapse;">
					<?php foreach ( $top_issues as $issue ) :
						$issue_name    = isset( $issue['name'] ) ? (string) $issue['name'] : '';
						$issue_reason  = isset( $issue['failure_reason'] ) ? (string) $issue['failure_reason'] : '';
						$issue_url     = isset( $issue['detail_url'] ) ? (string) $issue['detail_url'] : '#';
						$issue_is_core = ! empty( $issue['is_core'] );
						$issue_family  = isset( $issue['family'] ) ? sanitize_key( (string) $issue['family'] ) : '';
					?>
					<tr style="border-top: 1px solid var(--wps-gray-100);">
						<td style="padding: var(--wps-space-4) var(--wps-space-3) var(--wps-space-4) 0; width: 1.25rem; vertical-align: top;">
							<span style="color: var(--wps-status-fail);">✕</span>
						</td>
						<td style="padding: var(--wps-space-4) var(--wps-space-4) var(--wps-space-4) 0;">
							<div style="display: flex; align-items: center; gap: var(--wps-space-3); flex-wrap: wrap;">
								<span style="font-weight: var(--wps-font-weight-medium); color: var(--wps-gray-900);"><?php echo esc_html( $issue_name ); ?></span>
								<?php if ( $issue_is_core ) : ?>
									<span class="wps-badge wps-badge--core"><?php esc_html_e( 'Core', 'wpshadow' ); ?></span>
								<?php endif; ?>
								<?php if ( '' !== $issue_family ) : ?>
									<span class="wps-badge" style="background: var(--wps-gray-100); color: var(--wps-gray-600);">
										<?php echo esc_html( ucwords( str_replace( '-', ' ', $issue_family ) ) ); ?>
									</span>
								<?php endif; ?>
							</div>
							<?php if ( '' !== $issue_reason ) : ?>
								<p style="margin: var(--wps-space-1) 0 0; font-size: var(--wps-text-sm); color: var(--wps-gray-600);">
									<?php echo esc_html( wp_trim_words( $issue_reason, 15 ) ); ?>
								</p>
							<?php endif; ?>
						</td>
						<td style="padding: var(--wps-space-4) 0; white-space: nowrap; text-align: right; vertical-align: top;">
							<a href="<?php echo esc_url( $issue_url ); ?>" class="wps-button wps-button--secondary" style="padding: var(--wps-space-2) var(--wps-space-4); font-size: var(--wps-text-sm);">
								<?php esc_html_e( 'Details →', 'wpshadow' ); ?>
							</a>
						</td>
					</tr>
					<?php endforeach; ?>
					<?php if ( $extra_issues > 0 ) : ?>
					<tr style="border-top: 1px solid var(--wps-gray-100);">
						<td colspan="3" style="padding: var(--wps-space-4) 0; text-align: center; font-size: var(--wps-text-sm); color: var(--wps-gray-600);">
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
				</table>
			</div>
		</div>
		<?php elseif ( 0 === $pending && $active > 0 ) : ?>
		<!-- All Clear -->
		<div class="wps-alert wps-alert--info" style="margin-bottom: var(--wps-space-8);">
			<div class="wps-alert-icon">✓</div>
			<div class="wps-alert-content">
				<strong><?php esc_html_e( 'All checks passed!', 'wpshadow' ); ?></strong>
				<p style="margin: var(--wps-space-2) 0 0; font-size: var(--wps-text-sm);">
					<?php esc_html_e( 'No issues found on the last run. Guardian will alert you if anything changes.', 'wpshadow' ); ?>
				</p>
			</div>
		</div>
		<?php else : ?>
		<div class="wps-alert wps-alert--info" style="margin-bottom: var(--wps-space-8);">
			<div class="wps-alert-icon">ℹ️</div>
			<div class="wps-alert-content">
				<strong><?php esc_html_e( 'No enabled tests are reporting yet.', 'wpshadow' ); ?></strong>
				<p style="margin: var(--wps-space-2) 0 0; font-size: var(--wps-text-sm);">
					<?php esc_html_e( 'Enable at least one check and run a scan to see dashboard feedback.', 'wpshadow' ); ?>
				</p>
			</div>
		</div>
		<?php endif; ?>

		<!-- Health by Area -->
		<h2 style="font-size: var(--wps-text-lg); font-weight: var(--wps-font-weight-semibold); margin-bottom: var(--wps-space-4); color: var(--wps-gray-800);">
			<?php esc_html_e( 'Health by Area', 'wpshadow' ); ?>
		</h2>
		<div class="wps-grid wps-grid--auto" style="margin-bottom: var(--wps-space-8);">
			<?php foreach ( $family_data as $fam_slug => $fam ) :
				$fam_active = $fam['total'] - $fam['disabled'];
				$fam_score  = $fam_active > 0 ? (int) round( ( $fam['passed'] / $fam_active ) * 100 ) : 100;
				$fam_color  = $fam['failed'] > 0
					? ( $fam_score < 60 ? 'var(--wps-status-fail)' : 'var(--wps-amber-500)' )
					: 'var(--wps-status-pass)';
				$fam_icon   = isset( $family_icons[ $fam_slug ] ) ? $family_icons[ $fam_slug ] : '📋';
			?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&family=' . rawurlencode( $fam_slug ) ) ); ?>"
			   class="wps-card"
			   style="text-decoration: none; color: inherit; transition: transform .2s ease, box-shadow .2s ease;"
			   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='var(--wps-shadow-lg)'"
			   onmouseout="this.style.transform='none'; this.style.boxShadow='var(--wps-shadow-sm)'">
				<div style="display: flex; align-items: center; gap: var(--wps-space-3); margin-bottom: var(--wps-space-3);">
					<div style="font-size: 1.5rem;"><?php echo esc_html( $fam_icon ); ?></div>
					<div style="font-size: var(--wps-text-sm); color: var(--wps-gray-600);">
						<?php echo esc_html( ucwords( str_replace( '-', ' ', $fam_slug ) ) ); ?>
					</div>
				</div>
				<div style="font-size: 2rem; font-weight: var(--wps-font-weight-bold); color: <?php echo esc_attr( $fam_color ); ?>; margin-bottom: var(--wps-space-2);">
					<?php echo esc_html( $fam_score ); ?>%
				</div>
				<div style="font-size: var(--wps-text-xs); color: var(--wps-gray-500); margin-bottom: var(--wps-space-3);">
					<?php
					printf(
						/* translators: %d: failed checks count */
						esc_html( _n( '%d issue', '%d issues', (int) $fam['failed'], 'wpshadow' ) ),
						(int) $fam['failed']
					);
					?>
				</div>
				<div style="font-size: var(--wps-text-xs); color: var(--wps-gray-500);">
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

		<div style="text-align: center; margin-top: var(--wps-space-6); margin-bottom: var(--wps-space-8);">
			<a href="<?php echo esc_url( $guardian_url ); ?>" class="wps-button wps-button--primary" style="font-size: var(--wps-text-base); padding: var(--wps-space-4) var(--wps-space-8);">
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

	if ( 'detail' === $page_subview ) {
		wpshadow_render_diagnostic_detail_v2();
		return;
	}

	$rows = wpshadow_get_diagnostics_activity_rows();

	// Stats for the header strip.
	$g_total    = count( $rows );
	$g_passed   = 0;
	$g_failed   = 0;

	foreach ( $rows as $row ) {
		$s = isset( $row['status_raw'] ) ? (string) $row['status_raw'] : '';
		if ( 'passed' === $s )     { $g_passed++; }
		elseif ( 'failed' === $s ) { $g_failed++; }
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

	<div class="wrap wpshadow-dashboard wps-page-container">

		<!-- Page Header -->
		<div class="wps-page-header">
			<div class="wps-page-header-icon">🛡️</div>
			<div class="wps-page-header-content">
				<h1><?php esc_html_e( 'Guardian', 'wpshadow' ); ?></h1>
				<p><?php esc_html_e( 'Monitor, manage, and drill into every diagnostic check running on your site.', 'wpshadow' ); ?></p>
			</div>
			<div class="wps-page-actions" style="margin-left: auto; margin-bottom: 0;">
				<?php if ( $g_failed > 0 ) : ?>
					<span style="font-size: var(--wps-text-sm); color: var(--wps-status-fail); font-weight: var(--wps-font-weight-medium);">
						<?php
						printf(
							/* translators: %d: number of failing checks */
							esc_html( _n( '%d issue', '%d issues', $g_failed, 'wpshadow' ) ),
							(int) $g_failed
						);
						?>
					</span>
				<?php else : ?>
					<span style="font-size: var(--wps-text-sm); color: var(--wps-status-pass); font-weight: var(--wps-font-weight-medium);">
						<?php esc_html_e( '✓ All clear', 'wpshadow' ); ?>
					</span>
				<?php endif; ?>
				<span style="font-size: var(--wps-text-sm); color: var(--wps-gray-500);">
					<?php
					printf(
						/* translators: %1$d: passed, %2$d: total */
						esc_html__( '%1$d / %2$d passed', 'wpshadow' ),
						(int) $g_passed,
						(int) $g_total
					);
					?>
				</span>
				<button
					id="wps-run-all"
					class="wps-button wps-button--primary"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_scan_nonce' ) ); ?>"
				>
					<?php esc_html_e( 'Run All Tests', 'wpshadow' ); ?>
				</button>
			</div>
		</div>

		<!-- Run-All Status Bar (hidden until running) -->
		<div id="wps-run-all-status" style="display:none; padding: var(--wps-space-4); background: var(--wps-gray-100); border-radius: var(--wps-radius-md); margin-bottom: var(--wps-space-8); font-size: var(--wps-text-sm); color: var(--wps-gray-700);"></div>

		<?php if ( ! empty( $priority_queue ) ) : ?>
		<div class="wps-card" style="margin-bottom: var(--wps-space-8); border-left: 4px solid var(--wps-status-fail);">
			<div class="wps-card-header">
				<h2 class="wps-card-title"><?php esc_html_e( 'Start Here: Top 3 Priorities', 'wpshadow' ); ?></h2>
				<p style="margin: var(--wps-space-2) 0 0; font-size: var(--wps-text-sm); color: var(--wps-gray-600);">
					<?php esc_html_e( 'Ordered by urgency: core failures, confidence, and fixability.', 'wpshadow' ); ?>
				</p>
			</div>
			<div class="wps-card-body">
				<ol style="margin: 0; padding-left: 1.25rem; display: grid; gap: var(--wps-space-4);">
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
						<div style="display: flex; flex-direction: column; gap: var(--wps-space-1);">
							<a href="<?php echo esc_url( $item_url ); ?>" style="font-weight: var(--wps-font-weight-semibold); text-decoration: none;">
								<?php echo esc_html( $item_name ); ?>
							</a>
							<div style="font-size: var(--wps-text-xs); color: var(--wps-gray-600);">
								<?php echo esc_html( implode( ' • ', $item_tokens ) ); ?>
							</div>
							<div style="font-size: var(--wps-text-xs); color: var(--wps-gray-500);">
								<?php echo esc_html( (string) $entry['reason'] ); ?>
							</div>
							<?php if ( '' !== $item_action ) : ?>
							<div style="font-size: var(--wps-text-xs); color: var(--wps-gray-700);">
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
			<label style="flex: 1; min-width: 200px;">
				<span style="font-size: var(--wps-text-sm);"><?php esc_html_e( 'Search', 'wpshadow' ); ?></span>
				<input
					type="search"
					id="wps-search-diagnostics"
					placeholder="<?php echo esc_attr__( 'Find a diagnostic...', 'wpshadow' ); ?>"
					style="width: 100%; margin-top: var(--wps-space-2);"
				/>
			</label>
			<div class="wps-filter-bar-spacer"></div>
			<label>
				<span style="font-size: var(--wps-text-sm);"><?php esc_html_e( 'Status', 'wpshadow' ); ?></span>
				<select id="wps-filter-result" style="margin-top: var(--wps-space-2);">
					<option value="all"><?php esc_html_e( 'All', 'wpshadow' ); ?></option>
					<option value="passed"><?php esc_html_e( 'Passed', 'wpshadow' ); ?></option>
					<option value="failed"><?php esc_html_e( 'Failed', 'wpshadow' ); ?></option>
					<option value="disabled"><?php esc_html_e( 'Disabled', 'wpshadow' ); ?></option>
				</select>
			</label>
			<label>
				<span style="font-size: var(--wps-text-sm);"><?php esc_html_e( 'Area', 'wpshadow' ); ?></span>
				<select id="wps-filter-family" style="margin-top: var(--wps-space-2);">
					<option value="all"><?php esc_html_e( 'All', 'wpshadow' ); ?></option>
					<?php foreach ( $families as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $preselect_family, $key ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
			<label>
				<span style="font-size: var(--wps-text-sm);"><?php esc_html_e( 'Confidence', 'wpshadow' ); ?></span>
				<select id="wps-filter-confidence" style="margin-top: var(--wps-space-2);">
					<option value="all"><?php esc_html_e( 'All', 'wpshadow' ); ?></option>
					<option value="high"><?php esc_html_e( 'High', 'wpshadow' ); ?></option>
					<option value="standard"><?php esc_html_e( 'Standard', 'wpshadow' ); ?></option>
					<option value="low"><?php esc_html_e( 'Low (Beta)', 'wpshadow' ); ?></option>
				</select>
			</label>
			<label style="display: flex; align-items: flex-end; gap: var(--wps-space-2);">
				<input type="checkbox" id="wps-filter-core" />
				<span style="font-size: var(--wps-text-sm);"><?php esc_html_e( 'Core Only', 'wpshadow' ); ?></span>
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
					$action_tokens[] = __( 'Unknown', 'wpshadow' );
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
						<span style="color: var(--wps-status-pass);">✓</span>
						<span><?php esc_html_e( 'Passed', 'wpshadow' ); ?></span>
					<?php elseif ( 'failed' === $status_raw ) : ?>
						<span style="color: var(--wps-status-fail);">✕</span>
						<span><?php esc_html_e( 'Issue Found', 'wpshadow' ); ?></span>
					<?php elseif ( 'disabled' === $status_raw ) : ?>
						<span style="color: var(--wps-gray-600);">−</span>
						<span><?php esc_html_e( 'Disabled', 'wpshadow' ); ?></span>
					<?php else : ?>
						<span><?php esc_html_e( 'Unknown', 'wpshadow' ); ?></span>
					<?php endif; ?>
				</div>
				<div style="font-size: var(--wps-text-xs); color: var(--wps-gray-600); line-height: 1.4; margin-top: var(--wps-space-2);">
					<?php echo esc_html( $actionability_summary ); ?>
				</div>
				<div class="wps-diagnostic-card-footer">
					<span><?php echo wp_kses_post( $last_run ); ?></span>
					<span style="color: var(--wps-gray-500);">→</span>
				</div>
			</a>
			<?php endforeach; ?>
		</div>

		<!-- No Results -->
		<div id="wps-no-results" class="wps-alert wps-alert--warning wps-hidden">
			<div class="wps-alert-icon">⚠️</div>
			<div class="wps-alert-content">
				<strong><?php esc_html_e( 'No diagnostics found', 'wpshadow' ); ?></strong>
				<p style="margin: var(--wps-space-2) 0 0; font-size: var(--wps-text-sm);">
					<?php esc_html_e( 'Try adjusting your filters to see results.', 'wpshadow' ); ?>
				</p>
			</div>
		</div>

	</div>

	<script>
		document.addEventListener( 'DOMContentLoaded', function() {
			if ( typeof WPShadowUI !== 'undefined' ) {
				WPShadowUI.init();
				<?php if ( '' !== $preselect_family ) : ?>
				// Pre-apply family filter from the ?family= URL parameter (linked from Dashboard).
				var familyEl = document.getElementById( 'wps-filter-family' );
				if ( familyEl ) {
					familyEl.value = '<?php echo esc_js( $preselect_family ); ?>';
					WPShadowUI.filterTable();
				}
				<?php endif; ?>
			}
		} );
	</script>

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
			<a href="<?php echo esc_url( $back_url ); ?>" class="wps-button wps-button--secondary" style="margin-top: var(--wps-space-6); display: inline-flex;">
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
			<a href="<?php echo esc_url( $back_url ); ?>" class="wps-button wps-button--secondary" style="margin-top: var(--wps-space-6); display: inline-flex;">
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

	$toggle_nonce = wp_create_nonce( 'wpshadow_scan_settings' );
	$run_nonce    = wp_create_nonce( 'wpshadow_scan_nonce' );

	// Frequency override.
	$freq_overrides = get_option( 'wpshadow_diagnostic_frequency_overrides', array() );
	$freq_overrides = is_array( $freq_overrides ) ? $freq_overrides : array();
	$frequency_str  = isset( $freq_overrides[ $class_name ] ) ? (string) $freq_overrides[ $class_name ] : 'default';
	$freq_nonce     = wp_create_nonce( 'wpshadow_scan_settings' );
	?>

	<div class="wps-page-container">

		<!-- Back Button -->
		<a href="<?php echo esc_url( $back_url ); ?>" class="wps-button wps-button--secondary" style="margin-bottom: var(--wps-space-8); display: inline-flex;">
			<?php esc_html_e( '← Back to Guardian', 'wpshadow' ); ?>
		</a>

		<!-- Header -->
		<div class="wps-page-header">
			<div class="wps-page-header-content">
				<h1><?php echo esc_html( $name ); ?></h1>
				<div style="display: flex; gap: var(--wps-space-3); flex-wrap: wrap; margin-top: var(--wps-space-3);">
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
						<?php if ( '' !== $description ) : ?>
							<p><?php echo esc_html( $description ); ?></p>
						<?php endif; ?>
						<?php if ( 'failed' === $status_raw && '' !== $failure_reason ) : ?>
							<div class="wps-alert wps-alert--danger" style="margin-top: var(--wps-space-6);">
								<div class="wps-alert-icon">✕</div>
								<div class="wps-alert-content">
									<strong><?php esc_html_e( 'Issue Detected', 'wpshadow' ); ?></strong>
									<p style="margin: var(--wps-space-2) 0 0; font-size: var(--wps-text-sm);">
										<?php echo esc_html( $failure_reason ); ?>
									</p>
								</div>
							</div>
						<?php endif; ?>
						<?php if ( ! empty( $failure_issues ) ) : ?>
							<div style="margin-top: var(--wps-space-6);">
								<strong><?php esc_html_e( 'Issues Found:', 'wpshadow' ); ?></strong>
								<ul style="margin-top: var(--wps-space-2); padding-left: 1.5rem;">
									<?php foreach ( $failure_issues as $issue ) : ?>
										<li style="margin-bottom: var(--wps-space-2);">
											<?php echo esc_html( trim( wp_strip_all_tags( $issue ), '- ' ) ); ?>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( 'failed' === $status_raw ) : ?>
				<div class="wps-card">
					<div class="wps-card-header">
						<h2 class="wps-card-title"><?php esc_html_e( 'How to Address This', 'wpshadow' ); ?></h2>
					</div>
					<div class="wps-card-body">
						<ol style="padding-left: 1.5rem; margin: 0;">
							<li style="margin-bottom: var(--wps-space-3);">
								<?php esc_html_e( 'Review the issue details above to understand what needs fixing.', 'wpshadow' ); ?>
							</li>
							<li style="margin-bottom: var(--wps-space-3);">
								<?php esc_html_e( 'Make the necessary changes to your site or configuration.', 'wpshadow' ); ?>
							</li>
							<li style="margin-bottom: 0;">
								<?php esc_html_e( 'Click "Run Now" in the Actions panel to verify the fix.', 'wpshadow' ); ?>
							</li>
						</ol>
						<div class="wps-alert wps-alert--info" style="margin-top: var(--wps-space-6);">
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
						<dl style="margin: 0; display: grid; gap: var(--wps-space-4);">
							<div>
								<dt style="font-size: var(--wps-text-sm); color: var(--wps-gray-600);"><?php esc_html_e( 'Result', 'wpshadow' ); ?></dt>
								<dd style="margin: var(--wps-space-1) 0 0; font-weight: var(--wps-font-weight-semibold);"><?php echo esc_html( $status ); ?></dd>
							</div>
							<div>
								<dt style="font-size: var(--wps-text-sm); color: var(--wps-gray-600);"><?php esc_html_e( 'Last Run', 'wpshadow' ); ?></dt>
								<dd style="margin: var(--wps-space-1) 0 0; font-weight: var(--wps-font-weight-semibold);"><?php echo wp_kses_post( $last_run ); ?></dd>
							</div>
							<div>
								<dt style="font-size: var(--wps-text-sm); color: var(--wps-gray-600);"><?php esc_html_e( 'Next Run', 'wpshadow' ); ?></dt>
								<dd style="margin: var(--wps-space-1) 0 0; font-weight: var(--wps-font-weight-semibold);"><?php echo wp_kses_post( $next_run ); ?></dd>
							</div>
							<div>
								<dt style="font-size: var(--wps-text-sm); color: var(--wps-gray-600);"><?php esc_html_e( 'Active', 'wpshadow' ); ?></dt>
								<dd style="margin: var(--wps-space-1) 0 0; font-weight: var(--wps-font-weight-semibold);"><?php echo esc_html( $is_enabled ? __( 'Yes', 'wpshadow' ) : __( 'No', 'wpshadow' ) ); ?></dd>
							</div>
						</dl>
					</div>
				</div>

				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title"><?php esc_html_e( 'Actions', 'wpshadow' ); ?></h3>
					</div>
					<div class="wps-card-body">
						<div style="display: flex; flex-direction: column; gap: var(--wps-space-3);">
							<button
								class="wps-button wps-button--primary"
								data-action="run-diagnostic"
								data-class-name="<?php echo esc_attr( $class_name ); ?>"
								data-nonce="<?php echo esc_attr( $run_nonce ); ?>"
							>
								<?php esc_html_e( 'Run Now', 'wpshadow' ); ?>
							</button>
							<button
								class="wps-button wps-button--secondary"
								data-action="toggle-diagnostic"
								data-class-name="<?php echo esc_attr( $class_name ); ?>"
								data-nonce="<?php echo esc_attr( $toggle_nonce ); ?>"
								data-enabled="<?php echo esc_attr( $is_enabled ? '1' : '0' ); ?>"
							>
								<?php echo esc_html( $is_enabled ? __( 'Disable This Check', 'wpshadow' ) : __( 'Enable This Check', 'wpshadow' ) ); ?>
							</button>
						</div>
						<div data-status-message style="margin-top: var(--wps-space-3); font-size: var(--wps-text-sm); color: var(--wps-gray-600); min-height: 1.2em;"></div>
					</div>
				</div>

				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title"><?php esc_html_e( 'Schedule', 'wpshadow' ); ?></h3>
					</div>
					<div class="wps-card-body">
						<label style="display: block; margin-bottom: var(--wps-space-4);">
							<span style="display: block; font-size: var(--wps-text-sm); color: var(--wps-gray-600); margin-bottom: var(--wps-space-2);"><?php esc_html_e( 'Run Frequency', 'wpshadow' ); ?></span>
							<select id="wps-frequency-select" data-class-name="<?php echo esc_attr( $class_name ); ?>" data-nonce="<?php echo esc_attr( $freq_nonce ); ?>" style="width: 100%;">
								<option value="default"  <?php selected( $frequency_str, 'default' ); ?>><?php esc_html_e( 'Default', 'wpshadow' ); ?></option>
								<option value="daily"    <?php selected( $frequency_str, 'daily' ); ?>><?php esc_html_e( 'Daily', 'wpshadow' ); ?></option>
								<option value="weekly"   <?php selected( $frequency_str, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'wpshadow' ); ?></option>
								<option value="monthly"  <?php selected( $frequency_str, 'monthly' ); ?>><?php esc_html_e( 'Monthly', 'wpshadow' ); ?></option>
							</select>
						</label>
						<button id="wps-save-frequency" class="wps-button wps-button--secondary" style="width: 100%;">
							<?php esc_html_e( 'Save', 'wpshadow' ); ?>
						</button>
						<div id="wps-frequency-status" data-status-message style="margin-top: var(--wps-space-3); font-size: var(--wps-text-sm); color: var(--wps-gray-600); min-height: 1.2em;"></div>
					</div>
				</div>
				<?php
				// Treatment auditability: show whether an automated fix exists for this diagnostic.
				$tx_class    = null;
				$tx_maturity = null;
				$tx_risk     = null;
				if ( class_exists( '\\WPShadow\\Treatments\\Treatment_Registry' ) ) {
					$tx_class = \WPShadow\Treatments\Treatment_Registry::get_treatment( $selected_run_key );
					if ( null !== $tx_class && class_exists( '\\WPShadow\\Core\\Treatment_Metadata' ) ) {
						$tx_finding_id = method_exists( $tx_class, 'get_finding_id' )
							? sanitize_key( (string) $tx_class::get_finding_id() )
							: $selected_run_key;
						$tx_meta       = \WPShadow\Core\Treatment_Metadata::get( $tx_finding_id );
						$tx_maturity   = isset( $tx_meta['maturity'] ) ? (string) $tx_meta['maturity'] : null;
						$tx_risk       = isset( $tx_meta['risk_level'] ) ? (string) $tx_meta['risk_level'] : null;
					}
				}
				?>
				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title"><?php esc_html_e( 'Fix Available', 'wpshadow' ); ?></h3>
					</div>
					<div class="wps-card-body">
						<?php if ( null === $tx_class ) : ?>
							<p style="font-size: var(--wps-text-sm); color: var(--wps-gray-600); margin: 0;">
								<?php esc_html_e( 'No automated fix available. Manual resolution required.', 'wpshadow' ); ?>
							</p>
						<?php elseif ( 'shipped' === $tx_maturity ) : ?>
							<div style="display: flex; flex-direction: column; gap: var(--wps-space-3);">
								<div style="display: flex; align-items: center; gap: var(--wps-space-2); flex-wrap: wrap;">
									<span class="wps-badge wps-badge--pass">✓ <?php esc_html_e( 'Automated', 'wpshadow' ); ?></span>
									<?php if ( $tx_risk ) : ?>
										<span class="wps-badge wps-badge--confidence-<?php echo esc_attr( $tx_risk ); ?>">
											<?php echo esc_html( ucfirst( $tx_risk ) . ' ' . __( 'Risk', 'wpshadow' ) ); ?>
										</span>
									<?php endif; ?>
								</div>
								<p style="font-size: var(--wps-text-sm); color: var(--wps-gray-600); margin: 0;">
									<?php esc_html_e( 'WPShadow can apply this fix automatically from the Treatments page.', 'wpshadow' ); ?>
								</p>
							</div>
						<?php else : ?>
							<div style="display: flex; flex-direction: column; gap: var(--wps-space-3);">
								<span class="wps-badge" style="background: var(--wps-amber-100, #fef3c7); color: var(--wps-amber-700, #92400e); width: fit-content;">
									<?php esc_html_e( 'Guidance Only', 'wpshadow' ); ?>
								</span>
								<p style="font-size: var(--wps-text-sm); color: var(--wps-gray-600); margin: 0;">
									<?php esc_html_e( 'Step-by-step instructions are provided. No automated change is made.', 'wpshadow' ); ?>
								</p>
							</div>
						<?php endif; ?>
					</div>
				</div>

			</div>

		</div>

	</div>

	<?php
}
