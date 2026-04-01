<?php
/**
 * Dashboard Gauge Report View
 *
 * Renders detailed report pages for the overall dashboard gauge and the
 * nine category gauges.
 *
 * @package WPShadow
 * @subpackage Reports
 */

use WPShadow\Core\Form_Param_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once WPSHADOW_PATH . 'includes/systems/core/functions-category-metadata.php';

if ( ! function_exists( 'wpshadow_format_dashboard_gauge_report_time' ) ) {
	/**
	 * Format dashboard gauge report timestamps.
	 *
	 * @since  0.6090.1200
	 * @param  int $timestamp Unix timestamp.
	 * @return string
	 */
	function wpshadow_format_dashboard_gauge_report_time( int $timestamp ): string {
		if ( $timestamp <= 0 ) {
			return esc_html__( 'Never', 'wpshadow' );
		}

		$now      = time();
		$relative = $timestamp > $now
			? sprintf(
				/* translators: %s: human time difference */
				esc_html__( 'in %s', 'wpshadow' ),
				human_time_diff( $now, $timestamp )
			)
			: sprintf(
				/* translators: %s: human time difference */
				esc_html__( '%s ago', 'wpshadow' ),
				human_time_diff( $timestamp, $now )
			);

		$precise = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );

		return sprintf(
			'<span title="%s">%s</span>',
			esc_attr( $precise ),
			esc_html( $relative )
		);
	}
}

if ( ! function_exists( 'wpshadow_get_dashboard_gauge_report_rows' ) ) {
	/**
	 * Get diagnostic rows for a dashboard gauge report.
	 *
	 * @since  0.6090.1200
	 * @param  string $category_key Dashboard gauge category.
	 * @return array<int, array<string, mixed>>
	 */
	function wpshadow_get_dashboard_gauge_report_rows( string $category_key ): array {
		if ( ! class_exists( '\WPShadow\Diagnostics\Diagnostic_Registry' ) ) {
			return array();
		}

		$test_states          = function_exists( 'wpshadow_get_diagnostic_test_states' ) ? wpshadow_get_diagnostic_test_states() : array();
		$disabled_diagnostics = get_option( 'wpshadow_disabled_diagnostic_classes', array() );
		$disabled_diagnostics = is_array( $disabled_diagnostics ) ? $disabled_diagnostics : array();
		$file_map             = \WPShadow\Diagnostics\Diagnostic_Registry::get_diagnostic_file_map();

		if ( empty( $file_map ) || ! is_array( $file_map ) ) {
			return array();
		}

		$rows            = array();
		$now             = time();
		$category_meta   = wpshadow_get_category_metadata();
		$cached_findings = function_exists( 'wpshadow_get_cached_findings' )
			? wpshadow_get_cached_findings()
			: get_option( 'wpshadow_site_findings', array() );

		if ( ! is_array( $cached_findings ) ) {
			$cached_findings = array();
		}

		if ( function_exists( 'wpshadow_index_findings_by_id' ) ) {
			$cached_findings = wpshadow_index_findings_by_id( $cached_findings );
		}

		$family_to_gauge = array(
			'security'      => 'security',
			'performance'   => 'performance',
			'seo'           => 'seo',
			'accessibility' => 'accessibility',
			'design'        => 'design',
			'settings'      => 'settings',
			'monitoring'    => 'monitoring',
			'monitor'       => 'monitoring',
			'workflows'     => 'workflows',
			'workflow'      => 'workflows',
			'automation'    => 'workflows',
			'automations'   => 'workflows',
			'code-quality'  => 'code-quality',
			'code_quality'  => 'code-quality',
		);

		foreach ( $file_map as $short_class => $diagnostic_data ) {
			if ( ! is_string( $short_class ) || '' === $short_class ) {
				continue;
			}

			$class_name = 0 === strpos( $short_class, 'WPShadow\\Diagnostics\\' )
				? $short_class
				: 'WPShadow\\Diagnostics\\' . $short_class;

			$file = isset( $diagnostic_data['file'] ) ? (string) $diagnostic_data['file'] : '';
			if ( ! class_exists( $class_name ) && '' !== $file && file_exists( $file ) ) {
				require_once $file;
			}

			$friendly_name = str_replace( '_', ' ', str_replace( 'Diagnostic_', '', $short_class ) );
			$friendly_name = ucwords( strtolower( $friendly_name ) );
			if ( class_exists( $class_name ) && method_exists( $class_name, 'get_title' ) ) {
				$title = (string) call_user_func( array( $class_name, 'get_title' ) );
				if ( '' !== trim( $title ) ) {
					$friendly_name = $title;
				}
			}

			$description = '';
			if ( class_exists( $class_name ) && method_exists( $class_name, 'get_description' ) ) {
				$description = (string) call_user_func( array( $class_name, 'get_description' ) );
			}

			$family = isset( $diagnostic_data['family'] ) ? sanitize_key( (string) $diagnostic_data['family'] ) : '';
			if ( '' === $family && class_exists( $class_name ) && method_exists( $class_name, 'get_family' ) ) {
				$family = sanitize_key( (string) call_user_func( array( $class_name, 'get_family' ) ) );
			}

			$run_key = sanitize_key(
				strtolower(
					str_replace( '_', '-', str_replace( 'WPShadow\\Diagnostics\\', '', $class_name ) )
				)
			);

			$last_run_raw = (int) get_option( 'wpshadow_last_run_' . $run_key, 0 );
			$frequency    = DAY_IN_SECONDS;

			if ( class_exists( '\WPShadow\Core\Diagnostic_Scheduler' ) ) {
				$schedule = \WPShadow\Core\Diagnostic_Scheduler::get_schedule( $run_key );
				if ( is_array( $schedule ) && isset( $schedule['frequency'] ) ) {
					$frequency = (int) $schedule['frequency'];
				}
			}

			$next_run_label = esc_html__( 'On first run', 'wpshadow' );
			$is_overdue     = false;
			if ( $last_run_raw > 0 ) {
				if ( 0 === $frequency ) {
					$next_run_label = esc_html__( 'On every request', 'wpshadow' );
				} else {
					$next_run_due_at = $last_run_raw + $frequency;
					if ( $next_run_due_at <= $now ) {
						$next_run_label = esc_html__( 'Overdue', 'wpshadow' );
						$is_overdue     = true;
					} else {
						$next_run_label = wpshadow_format_dashboard_gauge_report_time( $next_run_due_at );
					}
				}
			}

			$status_label   = esc_html__( 'Unknown', 'wpshadow' );
			$status_raw     = 'unknown';
			$gauge_key      = '';
			$finding_id     = '';
			$failure_reason = '';
			$severity       = '';
			$threat_level   = 0;

			if ( function_exists( 'wpshadow_get_valid_diagnostic_test_state' ) ) {
				$state = wpshadow_get_valid_diagnostic_test_state( $class_name, $now );
				if ( is_array( $state ) && isset( $state['status'] ) ) {
					$status         = (string) $state['status'];
					$finding_id     = isset( $state['finding_id'] ) ? sanitize_key( (string) $state['finding_id'] ) : '';
					$state_category = isset( $state['category'] ) ? sanitize_key( (string) $state['category'] ) : '';
					if ( '' !== $state_category && isset( $category_meta[ $state_category ] ) ) {
						$gauge_key = $state_category;
					}
					if ( 'passed' === $status ) {
						$status_label = esc_html__( 'Passed', 'wpshadow' );
						$status_raw   = 'passed';
					} elseif ( 'failed' === $status ) {
						$status_label = esc_html__( 'Failed', 'wpshadow' );
						$status_raw   = 'failed';
					}
				}
			}

			if ( esc_html__( 'Unknown', 'wpshadow' ) === $status_label && $last_run_raw > 0 && ! $is_overdue ) {
				$stored_state = isset( $test_states[ $class_name ] ) && is_array( $test_states[ $class_name ] )
					? $test_states[ $class_name ]
					: array();

				$stored_status = isset( $stored_state['status'] ) ? (string) $stored_state['status'] : '';
				if ( '' === $finding_id ) {
					$finding_id = isset( $stored_state['finding_id'] ) ? sanitize_key( (string) $stored_state['finding_id'] ) : '';
				}

				if ( 'passed' === $stored_status ) {
					$status_label = esc_html__( 'Passed', 'wpshadow' );
					$status_raw   = 'passed';
				} elseif ( 'failed' === $stored_status ) {
					$status_label = esc_html__( 'Failed', 'wpshadow' );
					$status_raw   = 'failed';
				}

				$stored_category = isset( $stored_state['category'] ) ? sanitize_key( (string) $stored_state['category'] ) : '';
				if ( '' !== $gauge_key && '' === $stored_category ) {
					$stored_category = $gauge_key;
				}
				if ( '' !== $stored_category && isset( $category_meta[ $stored_category ] ) ) {
					$gauge_key = $stored_category;
				}
			}

			if ( '' === $gauge_key ) {
				$family_key = sanitize_key( $family );
				if ( '' !== $family_key && isset( $family_to_gauge[ $family_key ] ) ) {
					$gauge_key = $family_to_gauge[ $family_key ];
				}
			}

			$family_key = sanitize_key( $family );
			if ( '' !== $family_key && isset( $family_to_gauge[ $family_key ] ) ) {
				$gauge_key = $family_to_gauge[ $family_key ];
			}

			if ( '' === $gauge_key ) {
				$gauge_key = 'overall';
			}

			if ( 'overall' !== $category_key && $gauge_key !== $category_key ) {
				continue;
			}

			if ( '' !== $finding_id && isset( $cached_findings[ $finding_id ] ) && is_array( $cached_findings[ $finding_id ] ) ) {
				$finding        = $cached_findings[ $finding_id ];
				$failure_reason = isset( $finding['description'] ) ? trim( wp_strip_all_tags( (string) $finding['description'] ) ) : '';
				$severity       = isset( $finding['severity'] ) ? sanitize_key( (string) $finding['severity'] ) : '';
				$threat_level   = isset( $finding['threat_level'] ) ? (int) $finding['threat_level'] : 0;
			}

			$rows[] = array(
				'run_key'        => $run_key,
				'finding_id'     => $finding_id,
				'name'           => $friendly_name,
				'class'          => $class_name,
				'enabled'        => ! in_array( $class_name, $disabled_diagnostics, true ),
				'family'         => $family,
				'gauge_key'      => $gauge_key,
				'description'    => $description,
				'failure_reason' => $failure_reason,
				'severity'       => $severity,
				'threat_level'   => $threat_level,
				'last_run'       => $last_run_raw > 0 ? wpshadow_format_dashboard_gauge_report_time( $last_run_raw ) : esc_html__( 'Never', 'wpshadow' ),
				'next_run'       => $next_run_label,
				'status'         => $status_label,
				'status_raw'     => $status_raw,
				'has_treatment'  => (bool) ( '' !== $finding_id && class_exists( '\WPShadow\Treatments\Treatment_Registry' ) && null !== \WPShadow\Treatments\Treatment_Registry::get_treatment( $finding_id ) ),
				'detail_url'     => function_exists( 'wpshadow_get_diagnostic_detail_admin_url' )
					? wpshadow_get_diagnostic_detail_admin_url( $run_key )
					: add_query_arg(
						array(
							'page'       => 'wpshadow-diagnostic',
							'diagnostic' => $run_key,
						),
						admin_url( 'admin.php' )
					),
			);
		}

		usort(
			$rows,
			static function ( array $left, array $right ): int {
				$order = array(
					'failed'  => 0,
					'unknown' => 1,
					'passed'  => 2,
				);

				$left_rank  = $order[ $left['status_raw'] ] ?? 3;
				$right_rank = $order[ $right['status_raw'] ] ?? 3;

				if ( $left_rank !== $right_rank ) {
					return $left_rank <=> $right_rank;
				}

				return strcasecmp( (string) $left['name'], (string) $right['name'] );
			}
		);

		return $rows;
	}
}

if ( ! function_exists( 'wpshadow_get_dashboard_gauge_report_summary' ) ) {
	/**
	 * Summarize dashboard gauge report rows.
	 *
	 * @since  0.6090.1200
	 * @param  array<int, array<string, mixed>> $rows Dashboard gauge report rows.
	 * @return array<string, int>
	 */
	function wpshadow_get_dashboard_gauge_report_summary( array $rows ): array {
		$summary = array(
			'total'    => count( $rows ),
			'passed'   => 0,
			'failed'   => 0,
			'unknown'  => 0,
			'disabled' => 0,
		);

		foreach ( $rows as $row ) {
			$status_raw = isset( $row['status_raw'] ) ? (string) $row['status_raw'] : 'unknown';
			if ( isset( $summary[ $status_raw ] ) ) {
				++$summary[ $status_raw ];
			}

			if ( isset( $row['enabled'] ) && ! $row['enabled'] ) {
				++$summary['disabled'];
			}
		}

		return $summary;
	}
}

if ( ! function_exists( 'wpshadow_render_dashboard_gauge_report_status' ) ) {
	/**
	 * Render colored dashboard gauge report status text.
	 *
	 * @since  0.6090.1200
	 * @param  string $status_label Display label.
	 * @param  string $status_raw Raw status key.
	 * @return string
	 */
	function wpshadow_render_dashboard_gauge_report_status( string $status_label, string $status_raw ): string {
		if ( 'passed' === $status_raw ) {
			return '<span style="color:#00a32a;font-weight:600;">' . esc_html( $status_label ) . '</span>';
		}

		if ( 'failed' === $status_raw ) {
			return '<span style="color:#d63638;font-weight:600;">' . esc_html( $status_label ) . '</span>';
		}

		return esc_html( $status_label );
	}
}

$report_slug   = Form_Param_Helper::get( 'report', 'key', '' );
$report_map    = function_exists( 'wpshadow_get_dashboard_gauge_report_map' ) ? wpshadow_get_dashboard_gauge_report_map() : array();
$report_config = isset( $report_map[ $report_slug ] ) && is_array( $report_map[ $report_slug ] ) ? $report_map[ $report_slug ] : null;

if ( ! is_array( $report_config ) ) {
	wp_die( esc_html__( 'Invalid dashboard gauge report requested.', 'wpshadow' ) );
}

$category_key       = isset( $report_config['category'] ) ? (string) $report_config['category'] : 'overall';
$category_meta      = wpshadow_get_category_metadata();
$rows               = wpshadow_get_dashboard_gauge_report_rows( $category_key );
$summary            = wpshadow_get_dashboard_gauge_report_summary( $rows );
$failed_rows        = array_values(
	array_filter(
		$rows,
		static function ( array $row ): bool {
			return isset( $row['status_raw'] ) && 'failed' === $row['status_raw'];
		}
	)
);
$dashboard_url      = admin_url( 'admin.php?page=wpshadow' );
$reports_url        = admin_url( 'admin.php?page=wpshadow-reports' );
$site_health_url    = admin_url( 'site-health.php' );
$category_breakdown = array();

if ( 'overall' === $category_key ) {
	foreach ( $report_map as $slug => $item ) {
		$item_category = isset( $item['category'] ) ? (string) $item['category'] : '';
		if ( 'overall' === $item_category || ! isset( $category_meta[ $item_category ] ) ) {
			continue;
		}

		$category_breakdown[ $item_category ] = array(
			'label'   => (string) $category_meta[ $item_category ]['label'],
			'report'  => (string) $slug,
			'total'   => 0,
			'passed'  => 0,
			'failed'  => 0,
			'unknown' => 0,
		);
	}

	foreach ( $rows as $row ) {
		$row_category = isset( $row['gauge_key'] ) ? (string) $row['gauge_key'] : '';
		if ( ! isset( $category_breakdown[ $row_category ] ) ) {
			continue;
		}

		++$category_breakdown[ $row_category ]['total'];
		$status_raw = isset( $row['status_raw'] ) ? (string) $row['status_raw'] : 'unknown';
		if ( isset( $category_breakdown[ $row_category ][ $status_raw ] ) ) {
			++$category_breakdown[ $row_category ][ $status_raw ];
		}
	}
}
?>

<div class="wrap wps-page-container">
	<?php
	wpshadow_render_page_header(
		(string) $report_config['title'],
		(string) $report_config['desc'],
		(string) $report_config['icon']
	);
	?>

	<div class="wps-card wps-mb-6">
		<div class="wps-card-body">
			<div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center; justify-content:space-between;">
				<div>
					<p style="margin:0; color:#50575e; max-width:760px;">
						<?php
						echo esc_html(
							'overall' === $category_key
								? __( 'This report brings together the diagnostics behind your nine WPShadow dashboard gauges so you can see the full picture in one place.', 'wpshadow' )
								: __( 'This report focuses on the diagnostics that feed this dashboard gauge, so you can understand each result and decide what to improve next.', 'wpshadow' )
						);
						?>
					</p>
				</div>
				<div style="display:flex; flex-wrap:wrap; gap:10px;">
					<a href="<?php echo esc_url( $dashboard_url ); ?>" class="wps-btn wps-btn--secondary"><?php esc_html_e( 'Back to Dashboard', 'wpshadow' ); ?></a>
					<a href="<?php echo esc_url( $reports_url ); ?>" class="wps-btn wps-btn--secondary"><?php esc_html_e( 'Back to Reports', 'wpshadow' ); ?></a>
					<?php if ( 'overall' === $category_key ) : ?>
						<a href="<?php echo esc_url( $site_health_url ); ?>" class="wps-btn wps-btn--secondary"><?php esc_html_e( 'Open WordPress Site Health', 'wpshadow' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<div class="wps-grid wps-grid-auto-320 wps-mb-6">
		<div class="wps-card">
			<div class="wps-card-body">
				<div class="wps-text-muted"><?php esc_html_e( 'Diagnostics In Scope', 'wpshadow' ); ?></div>
				<div style="font-size:32px; font-weight:700;"><?php echo (int) $summary['total']; ?></div>
			</div>
		</div>
		<div class="wps-card">
			<div class="wps-card-body">
				<div class="wps-text-muted"><?php esc_html_e( 'Passed', 'wpshadow' ); ?></div>
				<div style="font-size:32px; font-weight:700; color:#00a32a;"><?php echo (int) $summary['passed']; ?></div>
			</div>
		</div>
		<div class="wps-card">
			<div class="wps-card-body">
				<div class="wps-text-muted"><?php esc_html_e( 'Need Attention', 'wpshadow' ); ?></div>
				<div style="font-size:32px; font-weight:700; color:#d63638;"><?php echo (int) $summary['failed']; ?></div>
			</div>
		</div>
		<div class="wps-card">
			<div class="wps-card-body">
				<div class="wps-text-muted"><?php esc_html_e( 'Unknown Or Waiting', 'wpshadow' ); ?></div>
				<div style="font-size:32px; font-weight:700;"><?php echo (int) $summary['unknown']; ?></div>
			</div>
		</div>
	</div>

	<?php if ( 'overall' === $category_key && ! empty( $category_breakdown ) ) : ?>
		<div class="wps-card wps-mb-6">
			<div class="wps-card-header">
				<h2 class="wps-card-title"><?php esc_html_e( 'Gauge-by-Gauge Breakdown', 'wpshadow' ); ?></h2>
			</div>
			<div class="wps-card-body">
				<div class="wps-grid wps-grid-auto-320">
					<?php foreach ( $category_breakdown as $item ) : ?>
						<a href="
						<?php
						echo esc_url(
							add_query_arg(
								array(
									'page'   => 'wpshadow-reports',
									'report' => $item['report'],
								),
								admin_url( 'admin.php' )
							)
						);
						?>
									" class="wps-card wps-card-hover" style="display:block; color:inherit; text-decoration:none;">
							<div class="wps-card-body">
								<h3 style="margin-top:0;"><?php echo esc_html( $item['label'] ); ?></h3>
								<p style="margin:0 0 10px; color:#50575e;">
									<?php
									echo esc_html(
										sprintf(
											/* translators: 1: passed count, 2: failed count, 3: unknown count */
											__( 'Passed: %1$d, Need attention: %2$d, Unknown: %3$d', 'wpshadow' ),
											$item['passed'],
											$item['failed'],
											$item['unknown']
										)
									);
									?>
								</p>
								<span class="wps-btn wps-btn--secondary"><?php esc_html_e( 'Open This Report', 'wpshadow' ); ?></span>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $failed_rows ) ) : ?>
		<?php
		$autofix_nonce = wp_create_nonce( 'wpshadow_autofix' );
		$toggle_nonce  = wp_create_nonce( 'wpshadow_scan_settings' );
		$ignore_nonce  = wp_create_nonce( 'wpshadow_kanban' );
		?>
		<div class="wps-card wps-mb-6">
			<div class="wps-card-header">
				<h2 class="wps-card-title"><?php esc_html_e( 'Priority Findings', 'wpshadow' ); ?></h2>
			</div>
			<div class="wps-card-body">
				<div style="display:grid; gap:14px;">
					<?php foreach ( array_slice( $failed_rows, 0, 8 ) as $row ) : ?>
						<div style="border:1px solid #dcdcde; border-left:4px solid #d63638; border-radius:8px; padding:14px 16px;">
							<div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center; justify-content:space-between;">
								<strong>
									<a href="<?php echo esc_url( (string) $row['detail_url'] ); ?>" style="color:inherit; text-decoration:none;">
										<?php echo esc_html( (string) $row['name'] ); ?>
									</a>
								</strong>
								<span style="font-size:12px; color:#50575e;">
									<?php
									echo esc_html(
										! empty( $row['severity'] )
											? sprintf(
												/* translators: 1: severity label, 2: threat level number */
												__( '%1$s priority, threat level %2$d', 'wpshadow' ),
												ucwords( str_replace( '-', ' ', (string) $row['severity'] ) ),
												(int) $row['threat_level']
											)
											: __( 'Needs attention', 'wpshadow' )
									);
									?>
								</span>
							</div>
							<p style="margin:8px 0 0; color:#50575e;">
								<?php echo esc_html( '' !== (string) $row['failure_reason'] ? (string) $row['failure_reason'] : (string) $row['description'] ); ?>
							</p>
							<div class="wpshadow-priority-finding-actions" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:12px;">
								<a href="<?php echo esc_url( (string) $row['detail_url'] ); ?>" class="button button-secondary">
									<?php esc_html_e( 'Details', 'wpshadow' ); ?>
								</a>
								<?php if ( ! empty( $row['has_treatment'] ) && ! empty( $row['finding_id'] ) ) : ?>
									<button
										type="button"
										class="button button-primary wpshadow-priority-fix-now"
										data-finding-id="<?php echo esc_attr( (string) $row['finding_id'] ); ?>"
										data-nonce="<?php echo esc_attr( $autofix_nonce ); ?>"
									>
										<?php esc_html_e( 'Fix Now', 'wpshadow' ); ?>
									</button>
								<?php endif; ?>
								<button
									type="button"
									class="button wpshadow-priority-toggle-diagnostic"
									data-class-name="<?php echo esc_attr( (string) $row['class'] ); ?>"
									data-enabled="<?php echo esc_attr( ! empty( $row['enabled'] ) ? '1' : '0' ); ?>"
									data-nonce="<?php echo esc_attr( $toggle_nonce ); ?>"
								>
									<?php echo esc_html( ! empty( $row['enabled'] ) ? __( 'Disable', 'wpshadow' ) : __( 'Enable', 'wpshadow' ) ); ?>
								</button>
								<button
									type="button"
									class="button wpshadow-priority-ignore-finding"
									data-finding-id="<?php echo esc_attr( (string) $row['finding_id'] ); ?>"
									data-nonce="<?php echo esc_attr( $ignore_nonce ); ?>"
									<?php disabled( empty( $row['finding_id'] ) ); ?>
								>
									<?php esc_html_e( 'Ignore', 'wpshadow' ); ?>
								</button>
								<span class="wpshadow-priority-action-status" role="status" aria-live="polite" style="font-size:12px;color:#50575e;"></span>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="wps-card">
		<div class="wps-card-header">
			<h2 class="wps-card-title"><?php esc_html_e( 'Diagnostic Analysis', 'wpshadow' ); ?></h2>
		</div>
		<div class="wps-card-body">
			<?php if ( empty( $rows ) ) : ?>
				<p><?php esc_html_e( 'No diagnostics are currently mapped to this gauge.', 'wpshadow' ); ?></p>
			<?php else : ?>
				<div style="max-height: 680px; overflow:auto; border:1px solid #dcdcde; border-radius:8px;">
					<table class="widefat striped" style="margin:0;">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Diagnostic', 'wpshadow' ); ?></th>
								<th><?php esc_html_e( 'Status', 'wpshadow' ); ?></th>
								<th><?php esc_html_e( 'Last Run', 'wpshadow' ); ?></th>
								<th><?php esc_html_e( 'Next Run', 'wpshadow' ); ?></th>
								<th><?php esc_html_e( 'Latest Analysis', 'wpshadow' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $rows as $row ) : ?>
								<tr>
									<td>
										<a href="<?php echo esc_url( (string) $row['detail_url'] ); ?>">
											<?php echo esc_html( (string) $row['name'] ); ?>
										</a>
										<?php if ( isset( $row['enabled'] ) && ! $row['enabled'] ) : ?>
											<div style="font-size:12px; color:#757575;"><?php esc_html_e( 'Currently disabled', 'wpshadow' ); ?></div>
										<?php endif; ?>
									</td>
									<td><?php echo wp_kses_post( wpshadow_render_dashboard_gauge_report_status( (string) $row['status'], (string) $row['status_raw'] ) ); ?></td>
									<td><?php echo wp_kses_post( (string) $row['last_run'] ); ?></td>
									<td><?php echo wp_kses_post( (string) $row['next_run'] ); ?></td>
									<td><?php echo esc_html( '' !== (string) $row['failure_reason'] ? (string) $row['failure_reason'] : (string) $row['description'] ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( function_exists( 'wpshadow_render_page_activities' ) ) : ?>
		<?php wpshadow_render_page_activities( 'reports', 10 ); ?>
	<?php endif; ?>
</div>

<script>
jQuery(function($) {
	function priorityStatus($context, message, isError) {
		var $status = $context.closest('.wpshadow-priority-finding-actions').find('.wpshadow-priority-action-status').first();
		$status.text(message || '');
		$status.css('color', isError ? '#d63638' : '#50575e');
	}

	$(document).on('click', '.wpshadow-priority-fix-now', function(e) {
		e.preventDefault();
		var $button = $(this);
		var findingId = String($button.data('finding-id') || '');
		var nonce = String($button.data('nonce') || '');

		if (!findingId || !nonce) {
			priorityStatus($button, '<?php echo esc_js( __( 'This finding cannot be fixed from here yet.', 'wpshadow' ) ); ?>', true);
			return;
		}

		$button.prop('disabled', true);
		priorityStatus($button, '<?php echo esc_js( __( 'Applying fix...', 'wpshadow' ) ); ?>', false);

		$.post(ajaxurl, {
			action: 'wpshadow_autofix_finding',
			nonce: nonce,
			finding_id: findingId
		}).done(function(response) {
			if (response && response.success) {
				priorityStatus($button, (response.data && response.data.message) ? response.data.message : '<?php echo esc_js( __( 'Fix applied. Refreshing...', 'wpshadow' ) ); ?>', false);
				setTimeout(function() { window.location.reload(); }, 900);
				return;
			}

			priorityStatus($button, (response && response.data && response.data.message) ? response.data.message : '<?php echo esc_js( __( 'Could not apply this fix.', 'wpshadow' ) ); ?>', true);
		}).fail(function() {
			priorityStatus($button, '<?php echo esc_js( __( 'Could not apply this fix.', 'wpshadow' ) ); ?>', true);
		}).always(function() {
			$button.prop('disabled', false);
		});
	});

	$(document).on('click', '.wpshadow-priority-toggle-diagnostic', function(e) {
		e.preventDefault();
		var $button = $(this);
		var className = String($button.data('class-name') || '');
		var nonce = String($button.data('nonce') || '');
		var enabled = String($button.attr('data-enabled')) === '1';

		if (!className || !nonce) {
			priorityStatus($button, '<?php echo esc_js( __( 'Missing diagnostic information.', 'wpshadow' ) ); ?>', true);
			return;
		}

		$button.prop('disabled', true);
		priorityStatus($button, '<?php echo esc_js( __( 'Saving diagnostic setting...', 'wpshadow' ) ); ?>', false);

		$.post(ajaxurl, {
			action: 'wpshadow_toggle_diagnostic',
			nonce: nonce,
			class_name: className,
			enable: enabled ? 0 : 1
		}).done(function(response) {
			if (response && response.success) {
				$button.attr('data-enabled', enabled ? '0' : '1');
				$button.text(enabled ? '<?php echo esc_js( __( 'Enable', 'wpshadow' ) ); ?>' : '<?php echo esc_js( __( 'Disable', 'wpshadow' ) ); ?>');
				priorityStatus($button, enabled ? '<?php echo esc_js( __( 'Diagnostic disabled.', 'wpshadow' ) ); ?>' : '<?php echo esc_js( __( 'Diagnostic enabled.', 'wpshadow' ) ); ?>', false);
				return;
			}

			priorityStatus($button, (response && response.data && response.data.message) ? response.data.message : '<?php echo esc_js( __( 'Could not update this diagnostic.', 'wpshadow' ) ); ?>', true);
		}).fail(function() {
			priorityStatus($button, '<?php echo esc_js( __( 'Could not update this diagnostic.', 'wpshadow' ) ); ?>', true);
		}).always(function() {
			$button.prop('disabled', false);
		});
	});

	$(document).on('click', '.wpshadow-priority-ignore-finding', function(e) {
		e.preventDefault();
		var $button = $(this);
		var findingId = String($button.data('finding-id') || '');
		var nonce = String($button.data('nonce') || '');

		if (!findingId || !nonce) {
			priorityStatus($button, '<?php echo esc_js( __( 'This finding cannot be ignored from here yet.', 'wpshadow' ) ); ?>', true);
			return;
		}

		$button.prop('disabled', true);
		priorityStatus($button, '<?php echo esc_js( __( 'Ignoring finding...', 'wpshadow' ) ); ?>', false);

		$.post(ajaxurl, {
			action: 'wpshadow_change_finding_status',
			nonce: nonce,
			finding_id: findingId,
			new_status: 'ignored'
		}).done(function(response) {
			if (response && response.success) {
				priorityStatus($button, (response.data && response.data.smart_action_desc) ? response.data.smart_action_desc : '<?php echo esc_js( __( 'Finding ignored. Refreshing...', 'wpshadow' ) ); ?>', false);
				setTimeout(function() { window.location.reload(); }, 900);
				return;
			}

			priorityStatus($button, (response && response.data && response.data.message) ? response.data.message : '<?php echo esc_js( __( 'Could not ignore this finding.', 'wpshadow' ) ); ?>', true);
		}).fail(function() {
			priorityStatus($button, '<?php echo esc_js( __( 'Could not ignore this finding.', 'wpshadow' ) ); ?>', true);
		}).always(function() {
			$button.prop('disabled', false);
		});
	});
});
</script>