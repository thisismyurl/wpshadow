<?php
/**
 * Main Dashboard Page
 *
 * Renders the primary WPShadow dashboard with health gauges and diagnostics overview.
 *
 * @package WPShadow
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Core\Form_Param_Helper;

// Load category metadata functions (global aliases)
require_once WPSHADOW_PATH . 'includes/systems/core/functions-category-metadata.php';

/**
 * Build scheduler run key from fully-qualified diagnostic class name.
 *
 * @since  0.6093.1200
 * @param  string $class_name Diagnostic class name.
 * @return string
 */
function wpshadow_get_diagnostic_run_key_from_class( string $class_name ): string {
	$short_name = str_replace( 'WPShadow\\Diagnostics\\', '', $class_name );
	$short_name = strtolower( str_replace( '_', '-', $short_name ) );

	return sanitize_key( $short_name );
}

/**
 * Format a timestamp in human-friendly relative text with a precise title.
 *
 * @since  0.6093.1200
 * @param  int    $timestamp Unix timestamp.
 * @return string
 */
function wpshadow_format_human_time( int $timestamp ): string {
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

/**
 * Build diagnostics activity rows for dashboard display.
 *
 * @since  0.6093.1200
 * @return array<int, array<string, mixed>>
 */
function wpshadow_get_diagnostics_activity_rows(): array {
	if ( ! class_exists( '\\WPShadow\\Diagnostics\\Diagnostic_Registry' ) ) {
		return array();
	}

	$test_states = function_exists( 'wpshadow_get_diagnostic_test_states' )
		? wpshadow_get_diagnostic_test_states()
		: array();

	$disabled_diagnostics = get_option( 'wpshadow_disabled_diagnostic_classes', array() );
	$disabled_diagnostics = is_array( $disabled_diagnostics ) ? $disabled_diagnostics : array();

	$file_map = \WPShadow\Diagnostics\Diagnostic_Registry::get_diagnostic_file_map();
	if ( empty( $file_map ) || ! is_array( $file_map ) ) {
		return array();
	}

	$rows = array();
	$now  = time();
	$category_meta = wpshadow_get_category_metadata();
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
		'workflows'     => 'workflows',
		'code-quality'  => 'code-quality',
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

		$severity = '';
		if ( class_exists( $class_name ) && method_exists( $class_name, 'get_severity' ) ) {
			$severity = (string) call_user_func( array( $class_name, 'get_severity' ) );
		}

		$time_to_fix = 0;
		if ( class_exists( $class_name ) && method_exists( $class_name, 'get_time_to_fix_minutes' ) ) {
			$time_to_fix = (int) call_user_func( array( $class_name, 'get_time_to_fix_minutes' ) );
		}

		$impact = '';
		if ( class_exists( $class_name ) && method_exists( $class_name, 'get_impact' ) ) {
			$impact = (string) call_user_func( array( $class_name, 'get_impact' ) );
		}

		$family = '';
		if ( class_exists( $class_name ) && method_exists( $class_name, 'get_family' ) ) {
			$family = (string) call_user_func( array( $class_name, 'get_family' ) );
		}

		$run_key      = wpshadow_get_diagnostic_run_key_from_class( $class_name );
		$last_run_raw = (int) get_option( 'wpshadow_last_run_' . $run_key, 0 );

		$frequency = DAY_IN_SECONDS;
		if ( class_exists( '\\WPShadow\\Core\\Diagnostic_Scheduler' ) ) {
			$schedule = \WPShadow\Core\Diagnostic_Scheduler::get_schedule( $run_key );
			if ( is_array( $schedule ) && isset( $schedule['frequency'] ) ) {
				$frequency = (int) $schedule['frequency'];
			}
		}

		$next_run_label   = esc_html__( 'On first run', 'wpshadow' );
		$is_overdue       = false;
		$next_run_due_at  = 0;
		if ( $last_run_raw > 0 ) {
			if ( 0 === $frequency ) {
				$next_run_label = esc_html__( 'On every request', 'wpshadow' );
			} else {
				$next_run_due_at = $last_run_raw + $frequency;
				if ( $next_run_due_at <= $now ) {
					$next_run_label = esc_html__( 'Overdue', 'wpshadow' );
					$is_overdue     = true;
				} else {
					$next_run_label = wpshadow_format_human_time( $next_run_due_at );
				}
			}
		}

		$status_label = esc_html__( 'Unknown', 'wpshadow' );
		$status_raw   = 'unknown';
		$gauge_key    = '';
		$finding_id   = '';
		$failure_reason = '';
		$failure_issues = array();
		$explanation_sections = array();
		if ( function_exists( 'wpshadow_get_valid_diagnostic_test_state' ) ) {
			$state = wpshadow_get_valid_diagnostic_test_state( $class_name, $now );
			if ( is_array( $state ) && isset( $state['status'] ) ) {
				$status = (string) $state['status'];
				$finding_id = isset( $state['finding_id'] ) ? sanitize_key( (string) $state['finding_id'] ) : '';
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
				$mapped_gauge = $family_to_gauge[ $family_key ];
				if ( isset( $category_meta[ $mapped_gauge ] ) ) {
					$gauge_key = $mapped_gauge;
				}
			}
		}

		if ( '' === $gauge_key ) {
			$gauge_key = 'overall';
		}

		if ( '' !== $finding_id && isset( $cached_findings[ $finding_id ] ) && is_array( $cached_findings[ $finding_id ] ) ) {
			$finding = $cached_findings[ $finding_id ];
			$failure_reason = isset( $finding['description'] ) ? trim( wp_strip_all_tags( (string) $finding['description'] ) ) : '';
			if ( isset( $finding['details']['issues'] ) && is_array( $finding['details']['issues'] ) ) {
				$failure_issues = array_values( array_filter( $finding['details']['issues'], 'is_string' ) );
			}
			if ( isset( $finding['details']['explanation_sections'] ) && is_array( $finding['details']['explanation_sections'] ) ) {
				$explanation_sections = $finding['details']['explanation_sections'];
			}
		}

		if ( '' === $failure_reason && 'failed' === $status_raw ) {
			$normalized_run_key = sanitize_key( $run_key );
			$normalized_name    = strtolower( trim( wp_strip_all_tags( $friendly_name ) ) );

			foreach ( $cached_findings as $cached_finding ) {
				if ( ! is_array( $cached_finding ) ) {
					continue;
				}

				$cached_id    = isset( $cached_finding['id'] ) ? sanitize_key( (string) $cached_finding['id'] ) : '';
				$cached_title = isset( $cached_finding['title'] ) ? strtolower( trim( wp_strip_all_tags( (string) $cached_finding['title'] ) ) ) : '';
				$is_match     = false;

				if ( '' !== $cached_id && '' !== $normalized_run_key ) {
					$is_match = ( $cached_id === $normalized_run_key ) || ( false !== strpos( $cached_id, $normalized_run_key ) ) || ( false !== strpos( $normalized_run_key, $cached_id ) );
				}

				if ( ! $is_match && '' !== $cached_title && '' !== $normalized_name ) {
					$is_match = false !== strpos( $cached_title, $normalized_name ) || false !== strpos( $normalized_name, $cached_title );
				}

				if ( $is_match ) {
					$failure_reason = isset( $cached_finding['description'] ) ? trim( wp_strip_all_tags( (string) $cached_finding['description'] ) ) : '';
					if ( isset( $cached_finding['details']['issues'] ) && is_array( $cached_finding['details']['issues'] ) ) {
						$failure_issues = array_values( array_filter( $cached_finding['details']['issues'], 'is_string' ) );
					}
					if ( isset( $cached_finding['details']['explanation_sections'] ) && is_array( $cached_finding['details']['explanation_sections'] ) ) {
						$explanation_sections = $cached_finding['details']['explanation_sections'];
					}
					if ( '' !== $failure_reason ) {
						break;
					}
				}
			}
		}

		$gauge_label = isset( $category_meta[ $gauge_key ]['label'] )
			? (string) $category_meta[ $gauge_key ]['label']
			: (string) $category_meta['overall']['label'];

		$current_frequency = $frequency;
		$is_enabled = ! in_array( $class_name, $disabled_diagnostics, true )
			&& ! in_array( $short_class, $disabled_diagnostics, true );

		if ( ! $is_enabled ) {
			$status_label = esc_html__( 'Disabled', 'wpshadow' );
			$status_raw   = 'disabled';
		}

		$rows[] = array(
			'run_key'   => $run_key,
			'name'      => $friendly_name,
			'class'     => $class_name,
			'enabled'   => $is_enabled,
			'family'    => $family,
			'gauge_key' => $gauge_key,
			'gauge_label' => $gauge_label,
			'description' => $description,
			'severity'  => $severity,
			'time_to_fix' => $time_to_fix,
			'impact'    => $impact,
			'frequency' => $current_frequency,
			'failure_reason' => $failure_reason,
			'failure_issues' => $failure_issues,
			'explanation_sections' => $explanation_sections,
			'last_run_ts' => $last_run_raw,
			'next_run_ts' => $next_run_due_at,
			'next_run_sort' => $is_overdue ? -1 : $next_run_due_at,
			'last_run'  => $last_run_raw > 0 ? wpshadow_format_human_time( $last_run_raw ) : esc_html__( 'Never', 'wpshadow' ),
			'next_run'  => $next_run_label,
			'status'    => $status_label,
			'status_raw' => $status_raw,
			'detail_url' => wpshadow_get_diagnostic_detail_admin_url( $run_key ),
		);
	}

	usort(
		$rows,
		static function ( array $a, array $b ): int {
			return strcasecmp( (string) $a['name'], (string) $b['name'] );
		}
	);

	return $rows;
}

/**
 * Build the dedicated admin URL for a diagnostic detail page.
 *
 * @since  0.6093.1200
 * @param  string $run_key Diagnostic run key.
 * @return string
 */
function wpshadow_get_diagnostic_detail_admin_url( string $run_key ): string {
	return add_query_arg(
		array(
			'page'       => 'wpshadow-diagnostic',
			'diagnostic' => sanitize_key( $run_key ),
		),
		admin_url( 'admin.php' )
	);
}

/**
 * Get plain-language guidance for a diagnostic issue.
 *
 * @since  0.6091.1200
 * @param  string $run_key Diagnostic run key.
 * @param  string $issue   Raw issue text.
 * @return array{
 *     issue_text: string,
 *     explanation: string,
 *     kb_link: string
 * }
 */
function wpshadow_get_issue_guidance( string $run_key, string $issue ): array {
	$issue_text   = trim( wp_strip_all_tags( $issue ) );
	$explanation  = '';
	$kb_link      = '';

	return array(
		'issue_text'   => $issue_text,
		'explanation'  => $explanation,
		'kb_link'      => $kb_link,
	);
}

/**
 * Build user-friendly issue guidance list.
 *
 * @since  0.6091.1200
 * @param  string              $run_key  Diagnostic run key.
 * @param  array<int, string>  $issues   Diagnostic issues.
 * @return array<int, array<string, string>>
 */
function wpshadow_get_issue_guidance_list( string $run_key, array $issues ): array {
	$guidance = array();

	foreach ( $issues as $issue ) {
		if ( ! is_string( $issue ) ) {
			continue;
		}

		$entry = wpshadow_get_issue_guidance( $run_key, $issue );
		if ( '' === $entry['issue_text'] ) {
			continue;
		}

		$guidance[] = $entry;
	}

	return $guidance;
}

/**
 * Render selected diagnostic detail panel.
 *
 * @since  0.6093.1200
 * @param  array<int, array<string, mixed>> $rows Diagnostics activity rows.
 * @return void
 */
function wpshadow_render_selected_diagnostic_detail( array $rows ): void {
	$selected_run_key = Form_Param_Helper::get( 'diagnostic', 'key', '' );
	if ( '' === $selected_run_key ) {
		return;
	}

	$selected = null;
	foreach ( $rows as $row ) {
		if ( isset( $row['run_key'] ) && $selected_run_key === (string) $row['run_key'] ) {
			$selected = $row;
			break;
		}
	}

	if ( ! is_array( $selected ) ) {
		return;
	}

	$description = isset( $selected['description'] ) ? trim( (string) $selected['description'] ) : '';
	if ( '' === $description ) {
		$description = esc_html__( 'This diagnostic checks a specific part of your site setup and helps you spot opportunities to improve reliability, performance, and visitor trust.', 'wpshadow' );
	}

	$family_label = isset( $selected['family'] ) ? (string) $selected['family'] : '';
	$is_enabled   = isset( $selected['enabled'] ) ? (bool) $selected['enabled'] : true;
	$gauge_label  = isset( $selected['gauge_label'] ) ? (string) $selected['gauge_label'] : (string) __( 'Overall Health', 'wpshadow' );
	$run_key      = isset( $selected['run_key'] ) ? (string) $selected['run_key'] : '';
	$frequency    = isset( $selected['frequency'] ) ? (int) $selected['frequency'] : DAY_IN_SECONDS;
	$severity     = isset( $selected['severity'] ) ? (string) $selected['severity'] : '';
	$time_to_fix  = isset( $selected['time_to_fix'] ) ? (int) $selected['time_to_fix'] : 0;
	$impact       = isset( $selected['impact'] ) ? trim( (string) $selected['impact'] ) : '';
	$failure_reason = isset( $selected['failure_reason'] ) ? trim( (string) $selected['failure_reason'] ) : '';
	$failure_issues = isset( $selected['failure_issues'] ) && is_array( $selected['failure_issues'] ) ? $selected['failure_issues'] : array();
	$explanation_sections = isset( $selected['explanation_sections'] ) && is_array( $selected['explanation_sections'] )
		? $selected['explanation_sections']
		: array();
	$back_url     = add_query_arg( array( 'page' => 'wpshadow' ), admin_url( 'admin.php' ) );
	$toggle_nonce = wp_create_nonce( 'wpshadow_scan_settings' );
	$run_nonce    = wp_create_nonce( 'wpshadow_security_scan' );

	$frequency_options = array(
		0       => __( 'Every request', 'wpshadow' ),
		3600    => __( 'Hourly', 'wpshadow' ),
		21600   => __( 'Every 6 hours', 'wpshadow' ),
		86400   => __( 'Daily', 'wpshadow' ),
		604800  => __( 'Weekly', 'wpshadow' ),
		2592000 => __( 'Monthly', 'wpshadow' ),
		7776000 => __( 'Quarterly', 'wpshadow' ),
	);

	if ( ! isset( $frequency_options[ $frequency ] ) ) {
		$frequency = DAY_IN_SECONDS;
	}

	$d_raw = isset( $selected['status_raw'] ) ? (string) $selected['status_raw'] : 'unknown';
	if ( '' === $failure_reason && 'failed' === $d_raw ) {
		$class_name = isset( $selected['class'] ) ? (string) $selected['class'] : '';
		if ( '' !== $class_name && class_exists( $class_name ) ) {
			try {
				$runtime_result = null;
				if ( method_exists( $class_name, 'check' ) ) {
					$runtime_result = call_user_func( array( $class_name, 'check' ) );
				} elseif ( method_exists( $class_name, 'execute' ) ) {
					$runtime_result = call_user_func( array( $class_name, 'execute' ) );
				}

				if ( is_array( $runtime_result ) && ! empty( $runtime_result['description'] ) ) {
					$failure_reason = trim( wp_strip_all_tags( (string) $runtime_result['description'] ) );
				}

				if ( is_array( $runtime_result ) && isset( $runtime_result['details']['issues'] ) && is_array( $runtime_result['details']['issues'] ) ) {
					$failure_issues = array_values( array_filter( $runtime_result['details']['issues'], 'is_string' ) );
				}

				if ( is_array( $runtime_result ) && isset( $runtime_result['details']['explanation_sections'] ) && is_array( $runtime_result['details']['explanation_sections'] ) ) {
					$explanation_sections = $runtime_result['details']['explanation_sections'];
				}
			} catch ( \Throwable $exception ) {
				// Keep UI resilient if the fallback check errors.
			}
		}
	}

	$issue_guidance = wpshadow_get_issue_guidance_list( $run_key, $failure_issues );

	$summary_text = isset( $explanation_sections['summary'] ) ? trim( (string) $explanation_sections['summary'] ) : '';
	if ( '' === $summary_text ) {
		$summary_text = sprintf(
			/* translators: %s: diagnostic name */
			__( 'This check looks at %s so you can quickly see whether this part of your site is healthy or needs attention. It is designed to give you a clear signal without requiring technical knowledge.', 'wpshadow' ),
			(string) $selected['name']
		);
	}

	$how_tested_text = isset( $explanation_sections['how_wp_shadow_tested'] ) ? trim( (string) $explanation_sections['how_wp_shadow_tested'] ) : '';
	if ( '' === $how_tested_text ) {
		$how_tested_text = __( 'WPShadow runs a focused automated check for this area by inspecting WordPress settings and diagnostic signals. It uses the same repeatable process each time, so you can compare results over time and track improvement confidently.', 'wpshadow' );
	}

	$why_matters_text = isset( $explanation_sections['why_it_matters'] ) ? trim( (string) $explanation_sections['why_it_matters'] ) : '';
	if ( '' === $why_matters_text ) {
		$why_matters_text = __( 'When this area is healthy, your site is easier to maintain and more dependable for visitors. Addressing warnings here helps reduce avoidable surprises later and keeps your website experience more consistent.', 'wpshadow' );
	}

	$how_to_fix_text = isset( $explanation_sections['how_to_fix_it'] ) ? trim( (string) $explanation_sections['how_to_fix_it'] ) : '';
	if ( '' === $how_to_fix_text ) {
		$how_to_fix_text = __( 'Use the issue details shown on this page as your action checklist, then run this diagnostic again to confirm the result changed. If needed, follow the linked guidance to complete the fix step by step at your own pace.', 'wpshadow' );
	}
	?>
	<div class="wps-card wps-mb-6">
		<div class="wps-card-header">
			<h2 class="wps-card-title"><?php echo esc_html( (string) $selected['name'] ); ?></h2>
		</div>
		<div class="wps-card-body">
			<div class="wps-mb-4" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
				<span style="font-size:12px;color:#4b5563;"><?php esc_html_e( 'Diagnostic Details', 'wpshadow' ); ?></span>
				<span style="font-size:12px;padding:4px 10px;border-radius:999px;background:#f0f6fc;color:#0b57d0;">
					<?php echo esc_html( $gauge_label ); ?>
				</span>
				<a href="<?php echo esc_url( $back_url ); ?>" aria-label="<?php esc_attr_e( 'Close diagnostic details', 'wpshadow' ); ?>" title="<?php esc_attr_e( 'Close', 'wpshadow' ); ?>" style="font-size:20px;line-height:1;text-decoration:none;color:#374151;padding:2px 6px;border-radius:4px;border:1px solid #d1d5db;">
					&times;
				</a>
			</div>

			<div class="wps-mb-4" style="border:1px solid #dcdcde;border-radius:8px;padding:14px;background:#ffffff;">
				<h3 style="margin-top:0;margin-bottom:10px;"><?php esc_html_e( 'Diagnostic Information', 'wpshadow' ); ?></h3>
				<p><strong><?php esc_html_e( 'Summary', 'wpshadow' ); ?></strong></p>
				<p><?php echo esc_html( $summary_text ); ?></p>
				<p><strong><?php esc_html_e( 'How WPShadow Tested', 'wpshadow' ); ?></strong></p>
				<p><?php echo esc_html( $how_tested_text ); ?></p>
				<p><strong><?php esc_html_e( 'Why it Matters', 'wpshadow' ); ?></strong></p>
				<p><?php echo esc_html( $why_matters_text ); ?></p>
				<p><strong><?php esc_html_e( 'How to Fix It', 'wpshadow' ); ?></strong></p>
				<p><?php echo esc_html( $how_to_fix_text ); ?></p>
				<p><strong><?php esc_html_e( 'Technical check label:', 'wpshadow' ); ?></strong></p>
				<p><?php echo esc_html( $description ); ?></p>
				<?php if ( '' !== $family_label ) : ?>
					<p>
						<strong><?php esc_html_e( 'Family:', 'wpshadow' ); ?></strong>
						<?php echo esc_html( $family_label ); ?>
					</p>
				<?php endif; ?>
				<?php if ( '' !== $severity ) : ?>
					<?php
					$severity_colors = array(
						'critical' => array( 'bg' => '#fef2f2', 'color' => '#991b1b', 'label' => __( 'Critical', 'wpshadow' ) ),
						'high'     => array( 'bg' => '#fff7ed', 'color' => '#9a3412', 'label' => __( 'High', 'wpshadow' ) ),
						'medium'   => array( 'bg' => '#fefce8', 'color' => '#854d0e', 'label' => __( 'Medium', 'wpshadow' ) ),
						'low'      => array( 'bg' => '#f0fdf4', 'color' => '#166534', 'label' => __( 'Low', 'wpshadow' ) ),
					);
					$sev_meta = isset( $severity_colors[ $severity ] ) ? $severity_colors[ $severity ] : array( 'bg' => '#f3f4f6', 'color' => '#374151', 'label' => ucfirst( $severity ) );
					?>
					<p>
						<strong><?php esc_html_e( 'Priority:', 'wpshadow' ); ?></strong>
						<span style="display:inline-block;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:600;background:<?php echo esc_attr( $sev_meta['bg'] ); ?>;color:<?php echo esc_attr( $sev_meta['color'] ); ?>;">
							<?php echo esc_html( $sev_meta['label'] ); ?>
						</span>
					</p>
				<?php endif; ?>
				<?php if ( '' !== $impact ) : ?>
					<p>
						<strong><?php esc_html_e( 'Value:', 'wpshadow' ); ?></strong>
						<?php echo esc_html( $impact ); ?>
					</p>
				<?php endif; ?>
				<?php if ( $time_to_fix > 0 ) : ?>
					<p>
						<strong><?php esc_html_e( 'Estimated time to fix:', 'wpshadow' ); ?></strong>
						<?php
						if ( $time_to_fix < 60 ) {
							printf(
								/* translators: %d: minutes */
								esc_html__( '%d minute(s)', 'wpshadow' ),
								$time_to_fix
							);
						} else {
							$hours   = (int) floor( $time_to_fix / 60 );
							$minutes = $time_to_fix % 60;
							if ( $minutes > 0 ) {
								printf(
									/* translators: 1: hours, 2: minutes */
									esc_html__( '%1$dh %2$dm', 'wpshadow' ),
									$hours,
									$minutes
								);
							} else {
								printf(
									/* translators: %d: hours */
									esc_html__( '%d hour(s)', 'wpshadow' ),
									$hours
								);
							}
						}
						?>
					</p>
				<?php endif; ?>
			</div>

			<div class="wps-mb-4" style="border:1px solid #dcdcde;border-radius:8px;padding:14px;background:#fafcff;">
				<h3 style="margin-top:0;margin-bottom:10px;"><?php esc_html_e( 'Current Status', 'wpshadow' ); ?></h3>
				<p>
					<strong><?php esc_html_e( 'Status:', 'wpshadow' ); ?></strong>
					<?php
					if ( 'passed' === $d_raw ) {
						echo '<span style="color:#00a32a;font-weight:600;">' . esc_html( (string) $selected['status'] ) . '</span>';
					} elseif ( 'failed' === $d_raw ) {
						echo '<span style="color:#d63638;font-weight:600;">' . esc_html( (string) $selected['status'] ) . '</span>';
					} else {
						echo esc_html( (string) $selected['status'] );
					}
					?>
				</p>
				<p><strong><?php esc_html_e( 'Last run:', 'wpshadow' ); ?></strong> <?php echo wp_kses_post( (string) $selected['last_run'] ); ?></p>
				<p><strong><?php esc_html_e( 'Next run:', 'wpshadow' ); ?></strong> <?php echo wp_kses_post( (string) $selected['next_run'] ); ?></p>
				<p>
					<strong><?php esc_html_e( 'Diagnostic is currently:', 'wpshadow' ); ?></strong>
					<span id="wpshadow-diagnostic-enabled-label">
						<?php echo esc_html( $is_enabled ? __( 'Enabled', 'wpshadow' ) : __( 'Disabled', 'wpshadow' ) ); ?>
					</span>
				</p>
				<?php if ( 'failed' === $d_raw ) : ?>
					<div style="margin-top:10px;padding:10px 12px;border-radius:6px;background:#fef2f2;border-left:4px solid #d63638;">
						<p style="margin:0 0 6px 0;"><strong><?php esc_html_e( 'Why this failed:', 'wpshadow' ); ?></strong></p>
						<p style="margin:0;">
							<?php
							echo esc_html(
								'' !== $failure_reason
									? $failure_reason
									: __( 'A specific issue was detected, but details are not available yet. Run this diagnostic now to refresh the latest failure reason.', 'wpshadow' )
							);
							?>
						</p>

						<?php if ( ! empty( $issue_guidance ) ) : ?>
							<div style="margin-top:12px;">
								<p style="margin:0 0 8px 0;"><strong><?php esc_html_e( 'What this means in plain language:', 'wpshadow' ); ?></strong></p>
								<ul style="margin:0 0 0 20px;list-style:disc;">
									<?php foreach ( $issue_guidance as $guidance_entry ) : ?>
										<li style="margin-bottom:10px;">
											<p style="margin:0 0 4px 0;"><?php echo esc_html( (string) $guidance_entry['issue_text'] ); ?></p>
											<?php if ( '' !== (string) $guidance_entry['explanation'] ) : ?>
												<p style="margin:0 0 4px 0;color:#374151;"><?php echo esc_html( (string) $guidance_entry['explanation'] ); ?></p>
											<?php endif; ?>
											<?php if ( '' !== (string) $guidance_entry['kb_link'] ) : ?>
												<p style="margin:0;">
													<a href="<?php echo esc_url( (string) $guidance_entry['kb_link'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Learn how to fix this', 'wpshadow' ); ?></a>
												</p>
											<?php endif; ?>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="wps-mb-4" style="border:1px solid #dcdcde;border-radius:8px;padding:14px;background:#ffffff;">
				<h3 style="margin-top:0;margin-bottom:10px;"><?php esc_html_e( 'Scheduling', 'wpshadow' ); ?></h3>
				<div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
					<label for="wpshadow-diagnostic-frequency">
						<strong><?php esc_html_e( 'Run frequency:', 'wpshadow' ); ?></strong>
					</label>
					<select
						id="wpshadow-diagnostic-frequency"
						data-run-key="<?php echo esc_attr( $run_key ); ?>"
						data-nonce="<?php echo esc_attr( $toggle_nonce ); ?>"
					>
						<?php foreach ( $frequency_options as $value => $label ) : ?>
							<option value="<?php echo esc_attr( (string) $value ); ?>" <?php selected( $frequency, (int) $value ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<button type="button" id="wpshadow-save-frequency-btn" class="button">
						<?php esc_html_e( 'Save Frequency', 'wpshadow' ); ?>
					</button>
				</div>
			</div>

			<div style="border:1px solid #dcdcde;border-radius:8px;padding:14px;background:#f6fbf7;">
				<h3 style="margin-top:0;margin-bottom:10px;"><?php esc_html_e( 'Actions', 'wpshadow' ); ?></h3>
				<div class="wps-flex wps-gap-3 wps-mt-2" style="flex-wrap:wrap;">
					<button
						type="button"
						id="wpshadow-toggle-diagnostic-btn"
						class="button"
						data-class-name="<?php echo esc_attr( (string) $selected['class'] ); ?>"
						data-enabled="<?php echo esc_attr( $is_enabled ? '1' : '0' ); ?>"
						data-nonce="<?php echo esc_attr( $toggle_nonce ); ?>"
					>
						<?php echo esc_html( $is_enabled ? __( 'Disable This Diagnostic', 'wpshadow' ) : __( 'Enable This Diagnostic', 'wpshadow' ) ); ?>
					</button>

					<button
						type="button"
						id="wpshadow-run-diagnostic-btn"
						class="button button-primary"
						data-class-name="<?php echo esc_attr( (string) $selected['class'] ); ?>"
						data-nonce="<?php echo esc_attr( $run_nonce ); ?>"
					>
						<?php esc_html_e( 'Run This Diagnostic Now', 'wpshadow' ); ?>
					</button>
				</div>
				<div id="wpshadow-diagnostic-action-status" role="status" aria-live="polite" class="wps-mt-3"></div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render the dedicated diagnostic detail admin page.
 *
 * @since  0.6093.1200
 * @return void
 */
function wpshadow_render_diagnostic_detail_page(): void {
$selected_run_key = Form_Param_Helper::get( 'diagnostic', 'key', '' );
$rows             = wpshadow_get_diagnostics_activity_rows();

?>
<div class="wrap wpshadow-dashboard wps-page-container">
<div class="wps-page-header-actions">
<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow' ) ); ?>" class="button wps-btn wps-btn--secondary wps-mr-3" aria-label="<?php esc_attr_e( 'Return to dashboard', 'wpshadow' ); ?>">
&larr; <?php esc_html_e( 'Back to Dashboard', 'wpshadow' ); ?>
</a>
</div>
<?php wpshadow_render_page_header(
__( 'Diagnostic Detail', 'wpshadow' ),
'',
'dashicons-search'
); ?>

<div class="wpshadow-dashboard-content">
<?php if ( '' === $selected_run_key ) : ?>
<div class="wps-card wps-mb-6">
<div class="wps-card-body">
<p><?php esc_html_e( 'No diagnostic was selected.', 'wpshadow' ); ?></p>
<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Return to Dashboard', 'wpshadow' ); ?></a></p>
</div>
</div>
<?php else : ?>
<?php wpshadow_render_selected_diagnostic_detail( $rows ); ?>
<?php endif; ?>
</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {

function updateToggleDiagnosticButtonState($button, enabled) {
$button.attr('data-enabled', enabled ? '1' : '0');
$button.text(
enabled
? '<?php echo esc_js( __( 'Disable This Diagnostic', 'wpshadow' ) ); ?>'
: '<?php echo esc_js( __( 'Enable This Diagnostic', 'wpshadow' ) ); ?>'
);

$('#wpshadow-diagnostic-enabled-label').text(
enabled
? '<?php echo esc_js( __( 'Enabled', 'wpshadow' ) ); ?>'
: '<?php echo esc_js( __( 'Disabled', 'wpshadow' ) ); ?>'
);
}

$(document).on('click', '#wpshadow-toggle-diagnostic-btn', function(event) {
event.preventDefault();

var $button = $(this);
var className = String($button.data('class-name') || '');
var nonce = String($button.data('nonce') || '');
var currentlyEnabled = String($button.attr('data-enabled')) === '1';
var nextEnabled = !currentlyEnabled;
var $status = $('#wpshadow-diagnostic-action-status');

if (!className || !nonce) {
$status.text('<?php echo esc_js( __( 'Missing diagnostic information. Please refresh and try again.', 'wpshadow' ) ); ?>');
return;
}

$button.prop('disabled', true);
$status.text('<?php echo esc_js( __( 'Saving diagnostic setting...', 'wpshadow' ) ); ?>');

$.post(ajaxurl, {
action: 'wpshadow_toggle_diagnostic',
nonce: nonce,
class_name: className,
enable: nextEnabled ? 1 : 0
}).done(function(response) {
if (response && response.success) {
updateToggleDiagnosticButtonState($button, nextEnabled);
$status.text(
nextEnabled
? '<?php echo esc_js( __( 'Diagnostic enabled successfully.', 'wpshadow' ) ); ?>'
: '<?php echo esc_js( __( 'Diagnostic disabled successfully.', 'wpshadow' ) ); ?>'
);
} else {
var message = (response && response.data && response.data.message)
? response.data.message
: '<?php echo esc_js( __( 'Could not update diagnostic setting.', 'wpshadow' ) ); ?>';
$status.text(message);
}
}).fail(function() {
$status.text('<?php echo esc_js( __( 'Could not update diagnostic setting.', 'wpshadow' ) ); ?>');
}).always(function() {
$button.prop('disabled', false);
});
});

$(document).on('click', '#wpshadow-run-diagnostic-btn', function(event) {
event.preventDefault();

var $button = $(this);
var className = String($button.data('class-name') || '');
var nonce = String($button.data('nonce') || '');
var $status = $('#wpshadow-diagnostic-action-status');

if (!className || !nonce) {
$status.text('<?php echo esc_js( __( 'Missing diagnostic information. Please refresh and try again.', 'wpshadow' ) ); ?>');
return;
}

$button.prop('disabled', true);
$status.text('<?php echo esc_js( __( 'Running this diagnostic now...', 'wpshadow' ) ); ?>');

$.post(ajaxurl, {
action: 'wpshadow_run_single_diagnostic',
nonce: nonce,
class_name: className
}).done(function(response) {
if (response && response.success) {
var message = (response.data && response.data.message)
? response.data.message
: '<?php echo esc_js( __( 'Diagnostic run completed.', 'wpshadow' ) ); ?>';
$status.text(message + ' <?php echo esc_js( __( 'Refreshing this page...', 'wpshadow' ) ); ?>');
setTimeout(function() {
window.location.reload();
}, 900);
} else {
var errorMessage = (response && response.data && response.data.message)
? response.data.message
: '<?php echo esc_js( __( 'Diagnostic run failed.', 'wpshadow' ) ); ?>';
$status.text(errorMessage);
}
}).fail(function() {
$status.text('<?php echo esc_js( __( 'Diagnostic run failed.', 'wpshadow' ) ); ?>');
}).always(function() {
$button.prop('disabled', false);
});
});

$(document).on('click', '#wpshadow-save-frequency-btn', function(event) {
event.preventDefault();

var $button = $(this);
var $select = $('#wpshadow-diagnostic-frequency');
var runKey = String($select.data('run-key') || '');
var nonce = String($select.data('nonce') || '');
var frequency = parseInt($select.val(), 10);
var $status = $('#wpshadow-diagnostic-action-status');

if (!runKey || !nonce || Number.isNaN(frequency)) {
$status.text('<?php echo esc_js( __( 'Missing scheduling information. Please refresh and try again.', 'wpshadow' ) ); ?>');
return;
}

$button.prop('disabled', true);
$status.text('<?php echo esc_js( __( 'Saving frequency...', 'wpshadow' ) ); ?>');

$.post(ajaxurl, {
action: 'wpshadow_set_diagnostic_frequency',
nonce: nonce,
run_key: runKey,
frequency: frequency
}).done(function(response) {
if (response && response.success) {
$status.text('<?php echo esc_js( __( 'Frequency saved. Future runs will follow this schedule.', 'wpshadow' ) ); ?>');
} else {
var message = (response && response.data && response.data.message)
? response.data.message
: '<?php echo esc_js( __( 'Could not save frequency.', 'wpshadow' ) ); ?>';
$status.text(message);
}
}).fail(function() {
$status.text('<?php echo esc_js( __( 'Could not save frequency.', 'wpshadow' ) ); ?>');
}).always(function() {
$button.prop('disabled', false);
});
});

});
</script>
<?php
}

/**
 * Render diagnostics recent activities table at the bottom of dashboard.
 *
 * @since  0.6093.1200
 * @return void
 */
function wpshadow_render_diagnostics_recent_activities(): void {
	$rows = wpshadow_get_diagnostics_activity_rows();

	if ( empty( $rows ) ) {
		return;
	}

	wpshadow_render_selected_diagnostic_detail( $rows );
	$family_options = array();
	foreach ( $rows as $row ) {
		$family = isset( $row['family'] ) ? sanitize_key( (string) $row['family'] ) : '';
		if ( '' === $family ) {
			continue;
		}

		$family_options[ $family ] = ucwords( str_replace( '-', ' ', $family ) );
	}
	ksort( $family_options );
	?>
	<div class="wps-card wps-mt-8">
		<div class="wps-card-header">
			<h2 class="wps-card-title"><?php esc_html_e( 'Diagnostic Status', 'wpshadow' ); ?></h2>
		</div>
		<div class="wps-card-body">
			<p><?php esc_html_e( 'Diagnostics run history and scheduling status.', 'wpshadow' ); ?></p>
			<div class="wpshadow-diagnostic-status-filters" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;margin:0 0 12px;">
				<label>
					<span style="display:block;font-size:12px;color:#4b5563;margin-bottom:4px;"><?php esc_html_e( 'Search', 'wpshadow' ); ?></span>
					<input type="search" id="wpshadow-diag-filter-search" class="regular-text" placeholder="<?php echo esc_attr__( 'Diagnostic name...', 'wpshadow' ); ?>" />
				</label>
				<label>
					<span style="display:block;font-size:12px;color:#4b5563;margin-bottom:4px;"><?php esc_html_e( 'Result', 'wpshadow' ); ?></span>
					<select id="wpshadow-diag-filter-result">
						<option value="all"><?php esc_html_e( 'All Results', 'wpshadow' ); ?></option>
						<option value="failed"><?php esc_html_e( 'Failed', 'wpshadow' ); ?></option>
						<option value="passed"><?php esc_html_e( 'Passed', 'wpshadow' ); ?></option>
						<option value="disabled"><?php esc_html_e( 'Disabled', 'wpshadow' ); ?></option>
						<option value="unknown"><?php esc_html_e( 'Unknown', 'wpshadow' ); ?></option>
					</select>
				</label>
				<label>
					<span style="display:block;font-size:12px;color:#4b5563;margin-bottom:4px;"><?php esc_html_e( 'Family', 'wpshadow' ); ?></span>
					<select id="wpshadow-diag-filter-family">
						<option value="all"><?php esc_html_e( 'All Families', 'wpshadow' ); ?></option>
						<?php foreach ( $family_options as $family_key => $family_label ) : ?>
							<option value="<?php echo esc_attr( $family_key ); ?>"><?php echo esc_html( $family_label ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<button type="button" id="wpshadow-diag-filter-clear" class="button button-secondary"><?php esc_html_e( 'Clear Filters', 'wpshadow' ); ?></button>
			</div>
			<div class="wpshadow-diagnostic-status-table-wrap" style="max-height: 480px; overflow: auto; border: 1px solid #dcdcde; border-radius: 6px;">
				<table id="wpshadow-diagnostic-status-table" class="widefat striped" style="margin:0;">
					<thead>
						<tr>
							<th style="width:64px;" aria-sort="none">
								<button type="button" class="wpshadow-sort-btn" data-sort-key="index" data-sort-type="number" style="all:unset;cursor:pointer;color:inherit;font-weight:600;">
									<?php esc_html_e( '#', 'wpshadow' ); ?>
								</button>
							</th>
							<th aria-sort="none">
								<button type="button" class="wpshadow-sort-btn" data-sort-key="name" data-sort-type="text" style="all:unset;cursor:pointer;color:inherit;font-weight:600;">
									<?php esc_html_e( 'Diagnostic', 'wpshadow' ); ?>
								</button>
							</th>
							<th aria-sort="none">
								<button type="button" class="wpshadow-sort-btn" data-sort-key="last-run" data-sort-type="number" style="all:unset;cursor:pointer;color:inherit;font-weight:600;">
									<?php esc_html_e( 'Last Run', 'wpshadow' ); ?>
								</button>
							</th>
							<th aria-sort="none">
								<button type="button" class="wpshadow-sort-btn" data-sort-key="next-run" data-sort-type="number" style="all:unset;cursor:pointer;color:inherit;font-weight:600;">
									<?php esc_html_e( 'Next Run', 'wpshadow' ); ?>
								</button>
							</th>
							<th aria-sort="none">
								<button type="button" class="wpshadow-sort-btn" data-sort-key="result" data-sort-type="number" style="all:unset;cursor:pointer;color:inherit;font-weight:600;">
									<?php esc_html_e( 'Result', 'wpshadow' ); ?>
								</button>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $rows as $index => $row ) : ?>
							<?php
							$status_order = 1;
							$r_raw        = isset( $row['status_raw'] ) ? (string) $row['status_raw'] : 'unknown';
							if ( 'failed' === $r_raw ) {
								$status_order = 0;
							} elseif ( 'disabled' === $r_raw ) {
								$status_order = 2;
							} elseif ( 'passed' === $r_raw ) {
								$status_order = 3;
							}
							?>
							<tr data-sort-index="<?php echo esc_attr( (string) ( (int) $index + 1 ) ); ?>" data-sort-name="<?php echo esc_attr( strtolower( (string) $row['name'] ) ); ?>" data-sort-last-run="<?php echo esc_attr( (string) (int) ( $row['last_run_ts'] ?? 0 ) ); ?>" data-sort-next-run="<?php echo esc_attr( (string) (int) ( $row['next_run_sort'] ?? 0 ) ); ?>" data-sort-result="<?php echo esc_attr( (string) $status_order ); ?>" data-filter-name="<?php echo esc_attr( strtolower( (string) $row['name'] ) ); ?>" data-filter-result="<?php echo esc_attr( $r_raw ); ?>" data-filter-family="<?php echo esc_attr( sanitize_key( (string) ( $row['family'] ?? '' ) ) ); ?>" data-filter-enabled="<?php echo esc_attr( ! empty( $row['enabled'] ) ? 'enabled' : 'disabled' ); ?>">
								<td class="wpshadow-col-index"><?php echo esc_html( (string) ( (int) $index + 1 ) ); ?></td>
								<td data-sort-value="<?php echo esc_attr( strtolower( (string) $row['name'] ) ); ?>">
									<a href="<?php echo esc_url( (string) $row['detail_url'] ); ?>">
										<?php echo esc_html( (string) $row['name'] ); ?>
									</a>
								</td>
								<td data-sort-value="<?php echo esc_attr( (string) (int) ( $row['last_run_ts'] ?? 0 ) ); ?>"><?php echo wp_kses_post( (string) $row['last_run'] ); ?></td>
								<td data-sort-value="<?php echo esc_attr( (string) (int) ( $row['next_run_sort'] ?? 0 ) ); ?>"><?php echo wp_kses_post( (string) $row['next_run'] ); ?></td>
								<td><?php
							if ( 'passed' === $r_raw ) {
								echo '<span style="color:#00a32a;font-weight:600;">' . esc_html( (string) $row['status'] ) . '</span>';
							} elseif ( 'failed' === $r_raw ) {
								echo '<span style="color:#d63638;font-weight:600;">' . esc_html( (string) $row['status'] ) . '</span>';
							} elseif ( 'disabled' === $r_raw ) {
								echo '<span style="color:#646970;font-weight:600;">' . esc_html( (string) $row['status'] ) . '</span>';
							} else {
								echo esc_html( (string) $row['status'] );
							}
						?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render the main WPShadow dashboard
 *
 * Requirements (Issue #562):
 * 1. Remove "Category Health" title
 * 2. Diagnostic check on page load:
 *    - If never run: Ask permission to run
 *    - If last run >5 minutes: Show progress bar with real-time updates
 *
 * @since  0.6093.1200
 * @return void
 */
function wpshadow_render_dashboard() {
	// Check for category drill-down (Issue #564)
	$category_filter = Form_Param_Helper::get( 'category', 'key', '' );
	$is_drilldown    = ! empty( $category_filter );

	// Get category metadata for title/details
	$category_meta    = wpshadow_get_category_metadata();
	$current_category = $is_drilldown && isset( $category_meta[ $category_filter ] )
		? $category_meta[ $category_filter ]
		: null;

	$last_scan        = get_option( 'wpshadow_last_quick_checks', 0 );
	$never_run        = empty( $last_scan );
	$five_minutes_ago = time() - ( 5 * MINUTE_IN_SECONDS );
	$needs_refresh    = ( $never_run || ( $last_scan > 0 && $last_scan < $five_minutes_ago ) );

	?>
	<div class="wrap wpshadow-dashboard wps-page-container">
		<?php if ( $is_drilldown && $current_category ) : ?>
			<div class="wps-page-header-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow' ) ); ?>" class="button wps-btn wps-btn--secondary wps-mr-3" aria-label="<?php esc_attr_e( 'Return to main dashboard', 'wpshadow' ); ?>">
					&larr; <?php esc_html_e( 'Back to Dashboard', 'wpshadow' ); ?>
				</a>
			</div>
			<?php wpshadow_render_page_header(
				sprintf(
					__( '%s Health', 'wpshadow' ),
					$current_category['label']
				),
				$current_category['description'],
				$current_category['icon'],
				$current_category['color']
			); ?>
		<?php else : ?>
			<?php wpshadow_render_page_header(
				__( 'WPShadow Dashboard', 'wpshadow' ),
				'',
				'dashicons-dashboard'
			); ?>
		<?php endif; ?>


		<div class="wpshadow-dashboard-content">
			<?php
			/**
			 * Health Gauges Section
			 *
			 * Renders all health category gauges
			 */
			do_action( 'wpshadow_dashboard_gauges', $category_filter );
			?>

			<?php
			/**
			 * Content After Gauges
			 *
			 * Used for additional dashboard widgets
			 */
			do_action( 'wpshadow_dashboard_after_content', $category_filter );
			?>

			<?php
			/**
			 * Activity History Section
			 *
			 * Shows recent activity and actions
			 */
			do_action( 'wpshadow_dashboard_activity', $category_filter );
			?>


			<!-- Dashboard Activity Log -->
			<?php
			if ( function_exists( 'wpshadow_render_page_activities' ) ) {
				wpshadow_render_page_activities( 'general', 3 );
			}

			wpshadow_render_diagnostics_recent_activities();
			?>
		</div>
	</div>

	<script type="text/javascript">
		jQuery(document).ready(function($) {

		var needsRefresh = <?php echo wp_json_encode( $needs_refresh ); ?>;
		var neverRun = <?php echo wp_json_encode( $never_run ); ?>;

		function updateToggleDiagnosticButtonState($button, enabled) {
			$button.attr('data-enabled', enabled ? '1' : '0');
			$button.text(
				enabled
					? '<?php echo esc_js( __( 'Disable This Diagnostic', 'wpshadow' ) ); ?>'
					: '<?php echo esc_js( __( 'Enable This Diagnostic', 'wpshadow' ) ); ?>'
			);

			$('#wpshadow-diagnostic-enabled-label').text(
				enabled
					? '<?php echo esc_js( __( 'Enabled', 'wpshadow' ) ); ?>'
					: '<?php echo esc_js( __( 'Disabled', 'wpshadow' ) ); ?>'
			);
		}

		$(document).on('click', '#wpshadow-toggle-diagnostic-btn', function(event) {
			event.preventDefault();

			var $button = $(this);
			var className = String($button.data('class-name') || '');
			var nonce = String($button.data('nonce') || '');
			var currentlyEnabled = String($button.attr('data-enabled')) === '1';
			var nextEnabled = !currentlyEnabled;
			var $status = $('#wpshadow-diagnostic-action-status');

			if (!className || !nonce) {
				$status.text('<?php echo esc_js( __( 'Missing diagnostic information. Please refresh and try again.', 'wpshadow' ) ); ?>');
				return;
			}

			$button.prop('disabled', true);
			$status.text('<?php echo esc_js( __( 'Saving diagnostic setting...', 'wpshadow' ) ); ?>');

			$.post(ajaxurl, {
				action: 'wpshadow_toggle_diagnostic',
				nonce: nonce,
				class_name: className,
				enable: nextEnabled ? 1 : 0
			}).done(function(response) {
				if (response && response.success) {
					updateToggleDiagnosticButtonState($button, nextEnabled);
					$status.text(
						nextEnabled
							? '<?php echo esc_js( __( 'Diagnostic enabled successfully.', 'wpshadow' ) ); ?>'
							: '<?php echo esc_js( __( 'Diagnostic disabled successfully.', 'wpshadow' ) ); ?>'
					);
				} else {
					var message = (response && response.data && response.data.message)
						? response.data.message
						: '<?php echo esc_js( __( 'Could not update diagnostic setting.', 'wpshadow' ) ); ?>';
					$status.text(message);
				}
			}).fail(function() {
				$status.text('<?php echo esc_js( __( 'Could not update diagnostic setting.', 'wpshadow' ) ); ?>');
			}).always(function() {
				$button.prop('disabled', false);
			});
		});

		$(document).on('click', '#wpshadow-run-diagnostic-btn', function(event) {
			event.preventDefault();

			var $button = $(this);
			var className = String($button.data('class-name') || '');
			var nonce = String($button.data('nonce') || '');
			var $status = $('#wpshadow-diagnostic-action-status');

			if (!className || !nonce) {
				$status.text('<?php echo esc_js( __( 'Missing diagnostic information. Please refresh and try again.', 'wpshadow' ) ); ?>');
				return;
			}

			$button.prop('disabled', true);
			$status.text('<?php echo esc_js( __( 'Running this diagnostic now...', 'wpshadow' ) ); ?>');

			$.post(ajaxurl, {
				action: 'wpshadow_run_single_diagnostic',
				nonce: nonce,
				class_name: className
			}).done(function(response) {
				if (response && response.success) {
					var message = (response.data && response.data.message)
						? response.data.message
						: '<?php echo esc_js( __( 'Diagnostic run completed.', 'wpshadow' ) ); ?>';
					$status.text(message + ' <?php echo esc_js( __( 'Refreshing this page...', 'wpshadow' ) ); ?>');
					setTimeout(function() {
						window.location.reload();
					}, 900);
				} else {
					var errorMessage = (response && response.data && response.data.message)
						? response.data.message
						: '<?php echo esc_js( __( 'Diagnostic run failed.', 'wpshadow' ) ); ?>';
					$status.text(errorMessage);
				}
			}).fail(function() {
				$status.text('<?php echo esc_js( __( 'Diagnostic run failed.', 'wpshadow' ) ); ?>');
			}).always(function() {
				$button.prop('disabled', false);
			});
		});

		$(document).on('click', '#wpshadow-save-frequency-btn', function(event) {
			event.preventDefault();

			var $button = $(this);
			var $select = $('#wpshadow-diagnostic-frequency');
			var runKey = String($select.data('run-key') || '');
			var nonce = String($select.data('nonce') || '');
			var frequency = parseInt($select.val(), 10);
			var $status = $('#wpshadow-diagnostic-action-status');

			if (!runKey || !nonce || Number.isNaN(frequency)) {
				$status.text('<?php echo esc_js( __( 'Missing scheduling information. Please refresh and try again.', 'wpshadow' ) ); ?>');
				return;
			}

			$button.prop('disabled', true);
			$status.text('<?php echo esc_js( __( 'Saving frequency...', 'wpshadow' ) ); ?>');

			$.post(ajaxurl, {
				action: 'wpshadow_set_diagnostic_frequency',
				nonce: nonce,
				run_key: runKey,
				frequency: frequency
			}).done(function(response) {
				if (response && response.success) {
					$status.text('<?php echo esc_js( __( 'Frequency saved. Future runs will follow this schedule.', 'wpshadow' ) ); ?>');
				} else {
					var message = (response && response.data && response.data.message)
						? response.data.message
						: '<?php echo esc_js( __( 'Could not save frequency.', 'wpshadow' ) ); ?>';
					$status.text(message);
				}
			}).fail(function() {
				$status.text('<?php echo esc_js( __( 'Could not save frequency.', 'wpshadow' ) ); ?>');
			}).always(function() {
				$button.prop('disabled', false);
			});
		});

		(function initDiagnosticStatusSorting() {
			var $table = $('#wpshadow-diagnostic-status-table');
			if (!$table.length) {
				return;
			}

			var $tbody = $table.find('tbody').first();
			var $headers = $table.find('thead th');
			var $searchFilter = $('#wpshadow-diag-filter-search');
			var $resultFilter = $('#wpshadow-diag-filter-result');
			var $familyFilter = $('#wpshadow-diag-filter-family');
			var filterDebounceTimer = null;

			var rows = $tbody.find('tr').map(function() {
				var rowEl = this;
				return {
					el: rowEl,
					name: String(rowEl.getAttribute('data-filter-name') || ''),
					result: String(rowEl.getAttribute('data-filter-result') || 'unknown'),
					family: String(rowEl.getAttribute('data-filter-family') || ''),
					index: parseInt(rowEl.getAttribute('data-sort-index') || '0', 10) || 0,
					sortValues: {
						index: parseFloat(rowEl.getAttribute('data-sort-index') || '0') || 0,
						name: String(rowEl.getAttribute('data-sort-name') || '').toLowerCase(),
						'last-run': parseFloat(rowEl.getAttribute('data-sort-last-run') || '0') || 0,
						'next-run': parseFloat(rowEl.getAttribute('data-sort-next-run') || '0') || 0,
						result: parseFloat(rowEl.getAttribute('data-sort-result') || '0') || 0
					}
				};
			}).get();

			function updateVisibleIndex() {
				var visibleIndex = 1;
				$.each(rows, function(_, row) {
					if (!row || !row.el) {
						return;
					}

					var indexCell = row.el.querySelector('.wpshadow-col-index');
					if (!indexCell) {
						return;
					}

					if (!row.el.hidden) {
						indexCell.textContent = String(visibleIndex);
						visibleIndex += 1;
					} else {
						indexCell.textContent = '';
					}
				});
			}

			function applyFiltersNow() {
				var search = String($searchFilter.val() || '').toLowerCase().trim();
				var result = String($resultFilter.val() || 'all');
				var family = String($familyFilter.val() || 'all');

				$.each(rows, function(_, row) {
					if (!row || !row.el) {
						return;
					}

					var matchesSearch = !search || row.name.indexOf(search) !== -1;
					var matchesResult = ('all' === result) || row.result === result;
					var matchesFamily = ('all' === family) || row.family === family;

					row.el.hidden = !(matchesSearch && matchesResult && matchesFamily);
				});

				updateVisibleIndex();
			}

			function scheduleFilterApply() {
				if (filterDebounceTimer) {
					window.clearTimeout(filterDebounceTimer);
				}

				filterDebounceTimer = window.setTimeout(function() {
					applyFiltersNow();
				}, 120);
			}

			function getSortValue($row, key, type) {
				var attr = String($row.data('sort-' + key) || '');
				if (type === 'number') {
					var parsed = parseFloat(attr);
					return Number.isNaN(parsed) ? 0 : parsed;
				}
				return attr.toLowerCase();
			}

			function setHeaderState($button, direction) {
				$headers.attr('aria-sort', 'none');
				$table.find('.wpshadow-sort-btn').each(function() {
					var $btn = $(this);
					$btn.find('.wpshadow-sort-indicator').remove();
				});

				if (!$button || !$button.length) {
					return;
				}

				var $th = $button.closest('th');
				$th.attr('aria-sort', direction === 'asc' ? 'ascending' : 'descending');
				$button.append('<span class="wpshadow-sort-indicator" aria-hidden="true" style="margin-left:6px;">' + (direction === 'asc' ? '▲' : '▼') + '</span>');
			}

			$(document).on('click', '.wpshadow-sort-btn', function(event) {
				event.preventDefault();
				var $button = $(this);
				var key = String($button.data('sort-key') || '');
				var type = String($button.data('sort-type') || 'text');
				if (!key) {
					return;
				}

				var currentDirection = String($button.attr('data-sort-direction') || '');
				var direction = currentDirection === 'asc' ? 'desc' : 'asc';

				$table.find('.wpshadow-sort-btn').attr('data-sort-direction', '');
				$button.attr('data-sort-direction', direction);

				rows.sort(function(a, b) {
					var av = (a && a.sortValues && a.sortValues[key] !== undefined) ? a.sortValues[key] : 0;
					var bv = (b && b.sortValues && b.sortValues[key] !== undefined) ? b.sortValues[key] : 0;

					if ('text' === type) {
						av = String(av).toLowerCase();
						bv = String(bv).toLowerCase();
					}

					if (av < bv) {
						return direction === 'asc' ? -1 : 1;
					}
					if (av > bv) {
						return direction === 'asc' ? 1 : -1;
					}

					return (a && a.index ? a.index : 0) - (b && b.index ? b.index : 0);
				});

				var fragment = document.createDocumentFragment();
				$.each(rows, function(_, row) {
					if (row && row.el) {
						fragment.appendChild(row.el);
					}
				});
				$tbody[0].appendChild(fragment);

				updateVisibleIndex();
				setHeaderState($button, direction);
			});

			$(document).on('input', '#wpshadow-diag-filter-search', function() {
				scheduleFilterApply();
			});

			$(document).on('change', '#wpshadow-diag-filter-result, #wpshadow-diag-filter-family', function() {
				applyFiltersNow();
			});

			$(document).on('click', '#wpshadow-diag-filter-clear', function(event) {
				event.preventDefault();
				$searchFilter.val('');
				$resultFilter.val('all');
				$familyFilter.val('all');
				applyFiltersNow();
			});

			applyFiltersNow();
		})();
		});
	</script>
	<?php
}
