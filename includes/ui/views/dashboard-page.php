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
		: get_option( 'wpshadow_diagnostic_test_states', array() );
	if ( ! is_array( $test_states ) ) {
		$test_states = array();
	}

	$diagnostics = \WPShadow\Diagnostics\Diagnostic_Registry::get_diagnostic_definitions();
	if ( empty( $diagnostics ) ) {
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
		'security'         => 'security',
		'performance'      => 'performance',
		'seo'              => 'seo',
		'accessibility'    => 'accessibility',
		'design'           => 'design',
		'settings'         => 'settings',
		'monitoring'       => 'monitoring',
		'workflows'        => 'workflows',
		'code-quality'     => 'code-quality',
		'database'         => 'performance',
		'wordpress-health' => 'wordpress-health',
	);

	foreach ( $diagnostics as $diagnostic ) {
		if ( ! is_array( $diagnostic ) ) {
			continue;
		}

		$class_name  = isset( $diagnostic['class'] ) ? (string) $diagnostic['class'] : '';
		$short_class = isset( $diagnostic['short_class'] ) ? (string) $diagnostic['short_class'] : '';
		if ( '' === $class_name ) {
			continue;
		}

		$friendly_name = isset( $diagnostic['title'] ) ? (string) $diagnostic['title'] : '';
		if ( '' === trim( $friendly_name ) ) {
			$friendly_name = str_replace( '_', ' ', str_replace( 'Diagnostic_', '', $short_class ) );
			$friendly_name = ucwords( strtolower( $friendly_name ) );
		}

		$description = isset( $diagnostic['description'] ) ? (string) $diagnostic['description'] : '';
		$severity    = isset( $diagnostic['severity'] ) ? (string) $diagnostic['severity'] : '';

		$time_to_fix = 0;
		if ( class_exists( $class_name ) && method_exists( $class_name, 'get_time_to_fix_minutes' ) ) {
			$time_to_fix = (int) call_user_func( array( $class_name, 'get_time_to_fix_minutes' ) );
		}

		$impact = '';
		if ( class_exists( $class_name ) && method_exists( $class_name, 'get_impact' ) ) {
			$impact = (string) call_user_func( array( $class_name, 'get_impact' ) );
		}

		$family = isset( $diagnostic['family'] ) ? (string) $diagnostic['family'] : '';

		$run_key      = isset( $diagnostic['run_key'] ) && '' !== (string) $diagnostic['run_key']
			? (string) $diagnostic['run_key']
			: wpshadow_get_diagnostic_run_key_from_class( $class_name );
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

		if ( esc_html__( 'Unknown', 'wpshadow' ) === $status_label ) {
			$stored_state = isset( $test_states[ $class_name ] ) && is_array( $test_states[ $class_name ] )
				? $test_states[ $class_name ]
				: array();

			$stored_status = isset( $stored_state['status'] ) ? (string) $stored_state['status'] : '';
			$stored_checked_at = isset( $stored_state['checked_at'] ) ? (int) $stored_state['checked_at'] : 0;

			if ( $last_run_raw <= 0 && $stored_checked_at > 0 ) {
				$last_run_raw = $stored_checked_at;
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
		$is_enabled        = ! empty( $diagnostic['enabled'] );

		if ( ! $is_enabled ) {
			$status_label = esc_html__( 'Disabled', 'wpshadow' );
			$status_raw   = 'disabled';
		}

		// Get confidence tier and core status.
		$is_core = false;
		$confidence = 'standard';
		if ( '' !== $class_name && class_exists( $class_name ) ) {
			if ( method_exists( $class_name, 'is_core' ) ) {
				$is_core = (bool) call_user_func( array( $class_name, 'is_core' ) );
			}
			if ( method_exists( $class_name, 'get_confidence' ) ) {
				$confidence = (string) call_user_func( array( $class_name, 'get_confidence' ) );
			}
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
			'last_run'  => $last_run_raw > 0 ? wpshadow_format_human_time( $last_run_raw ) : esc_html__( 'Not yet run', 'wpshadow' ),
			'next_run'  => $next_run_label,
			'status'    => $status_label,
			'status_raw' => $status_raw,
			'detail_url' => wpshadow_get_diagnostic_detail_admin_url( $run_key ),
			'is_core'   => $is_core,
			'confidence' => $confidence,
		);
	}

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
			'page'       => 'wpshadow-guardian',
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

	// Read frequency from the same option the settings Diagnostics tab writes to.
	$class_name        = isset( $selected['class'] ) ? (string) $selected['class'] : '';
	$freq_overrides    = get_option( 'wpshadow_diagnostic_frequency_overrides', array() );
	$freq_overrides    = is_array( $freq_overrides ) ? $freq_overrides : array();
	$frequency_str     = isset( $freq_overrides[ $class_name ] ) ? (string) $freq_overrides[ $class_name ] : 'default';
	$valid_freq_values = array( 'default', 'daily', 'weekly', 'monthly' );
	if ( ! in_array( $frequency_str, $valid_freq_values, true ) ) {
		$frequency_str = 'default';
	}

	$default_freq_label = 'daily';
	if ( '' !== $class_name && class_exists( $class_name ) && method_exists( $class_name, 'get_scan_frequency' ) ) {
		$default_freq_label = (string) call_user_func( array( $class_name, 'get_scan_frequency' ) );
	}

	$frequency_options = array(
		'default'   => sprintf(
			/* translators: %s: default frequency label */
			__( 'Default (%s)', 'wpshadow' ),
			ucfirst( $default_freq_label )
		),
		'daily'     => __( 'Daily', 'wpshadow' ),
		'weekly'    => __( 'Weekly', 'wpshadow' ),
		'monthly'   => __( 'Monthly', 'wpshadow' ),
	);

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

	$is_core = isset( $selected['is_core'] ) ? (bool) $selected['is_core'] : false;
	$confidence = isset( $selected['confidence'] ) ? (string) $selected['confidence'] : 'standard';
	?>
	<div class="wps-card wps-mb-6">
		<div class="wps-card-header">
			<div class="wps-card-header-row">
				<h2 class="wps-card-title wps-card-title--flush"><?php echo esc_html( (string) $selected['name'] ); ?></h2>
				<div class="wps-card-badge-row">
					<?php if ( $is_core ) : ?>
						<span class="wps-core-badge">
							<?php esc_html_e( 'Core Check', 'wpshadow' ); ?>
						</span>
					<?php endif; ?>
					<span class="wps-confidence-badge wps-confidence-badge--<?php echo esc_attr( $confidence ); ?>">
						<?php echo esc_html( ucfirst( $confidence ) ); ?>
					</span>
					<span class="wps-diagnostic-gauge-badge">
						<?php echo esc_html( $gauge_label ); ?>
					</span>
				</div>
			</div>
		</div>
		<div class="wps-card-body">
			<!-- Two-column 66/33 layout -->
			<div class="wps-diagnostic-detail-layout">

				<!-- Left column: 66% — Diagnostic Information -->
				<div class="wps-diagnostic-detail-main">
					<div class="wps-diagnostic-panel">
						<h3><?php esc_html_e( 'Diagnostic Information', 'wpshadow' ); ?></h3>
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
							$severity_meta = array(
								'critical' => array( 'class' => 'wps-diagnostic-priority-pill wps-diagnostic-priority-pill--critical', 'label' => __( 'Critical', 'wpshadow' ) ),
								'high'     => array( 'class' => 'wps-diagnostic-priority-pill wps-diagnostic-priority-pill--high', 'label' => __( 'High', 'wpshadow' ) ),
								'medium'   => array( 'class' => 'wps-diagnostic-priority-pill wps-diagnostic-priority-pill--medium', 'label' => __( 'Medium', 'wpshadow' ) ),
								'low'      => array( 'class' => 'wps-diagnostic-priority-pill wps-diagnostic-priority-pill--low', 'label' => __( 'Low', 'wpshadow' ) ),
							);
							$sev_meta = isset( $severity_meta[ $severity ] ) ? $severity_meta[ $severity ] : array( 'class' => 'wps-diagnostic-priority-pill', 'label' => ucfirst( $severity ) );
							?>
							<p>
								<strong><?php esc_html_e( 'Priority:', 'wpshadow' ); ?></strong>
								<span class="<?php echo esc_attr( $sev_meta['class'] ); ?>">
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
				</div><!-- /left column -->

				<!-- Right column: 33% — Status, Scheduling, Actions -->
				<div class="wps-diagnostic-detail-sidebar">

					<div class="wps-diagnostic-panel wps-diagnostic-panel--muted">
						<h3><?php esc_html_e( 'Current Status', 'wpshadow' ); ?></h3>
						<p>
							<strong><?php esc_html_e( 'Status:', 'wpshadow' ); ?></strong>
							<?php
							$status_text  = esc_html( (string) $selected['status'] );
							$status_class = 'wps-status-text';
							if ( 'passed' === $d_raw ) {
								$status_class .= ' wps-status-text--passed';
							} elseif ( 'failed' === $d_raw ) {
								$status_class .= ' wps-status-text--failed';
							} elseif ( 'disabled' === $d_raw ) {
								$status_class .= ' wps-status-text--muted';
							}
							echo '<span id="wpshadow-diagnostic-status-text" class="' . esc_attr( $status_class ) . '"'
								. ' data-original="' . esc_attr( (string) $selected['status'] ) . '"'
								. ' data-original-class="' . esc_attr( $status_class ) . '"'
								. '>' . $status_text . '</span>';
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
							<div class="wps-diagnostic-failure-box">
								<p class="wps-diagnostic-failure-title"><strong><?php esc_html_e( 'Why this failed:', 'wpshadow' ); ?></strong></p>
								<p class="wps-diagnostic-failure-text">
									<?php
									echo esc_html(
										'' !== $failure_reason
											? $failure_reason
											: __( 'A specific issue was detected, but details are not available yet. Run this diagnostic now to refresh the latest failure reason.', 'wpshadow' )
									);
									?>
								</p>

								<?php if ( ! empty( $issue_guidance ) ) : ?>
									<div class="wps-diagnostic-guidance">
										<p class="wps-diagnostic-guidance-title"><strong><?php esc_html_e( 'What this means in plain language:', 'wpshadow' ); ?></strong></p>
										<ul class="wps-diagnostic-guidance-list">
											<?php foreach ( $issue_guidance as $guidance_entry ) : ?>
												<li class="wps-diagnostic-guidance-item">
													<p><?php echo esc_html( (string) $guidance_entry['issue_text'] ); ?></p>
													<?php if ( '' !== (string) $guidance_entry['explanation'] ) : ?>
														<p class="wps-diagnostic-guidance-explanation"><?php echo esc_html( (string) $guidance_entry['explanation'] ); ?></p>
													<?php endif; ?>
													<?php if ( '' !== (string) $guidance_entry['kb_link'] ) : ?>
														<p class="wps-diagnostic-failure-text">
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

					<div class="wps-diagnostic-panel">
						<h3><?php esc_html_e( 'Scheduling', 'wpshadow' ); ?></h3>
						<div class="wps-diagnostic-inline-form">
							<label for="wpshadow-diagnostic-frequency">
								<strong><?php esc_html_e( 'Run frequency:', 'wpshadow' ); ?></strong>
							</label>
							<select
								id="wpshadow-diagnostic-frequency"
								data-class-name="<?php echo esc_attr( $class_name ); ?>"
								data-nonce="<?php echo esc_attr( $toggle_nonce ); ?>"
							>
								<?php foreach ( $frequency_options as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $frequency_str, $value ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<button type="button" id="wpshadow-save-frequency-btn" class="button">
								<?php esc_html_e( 'Save Frequency', 'wpshadow' ); ?>
							</button>
						</div>
					</div>

					<div class="wps-diagnostic-panel wps-diagnostic-panel--success">
						<h3><?php esc_html_e( 'Actions', 'wpshadow' ); ?></h3>
						<div class="wps-flex wps-gap-3 wps-mt-2 wps-diagnostic-actions">
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

				</div><!-- /right column -->

			</div><!-- /two-column layout -->
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
	<?php wpshadow_render_diagnostic_action_handlers_js(); ?>
	
	});
	</script>
	<?php
}

	/**
	 * Render shared JavaScript handlers for diagnostic detail actions.
	 *
	 * @return void
	 */
	function wpshadow_render_diagnostic_action_handlers_js(): void {
		?>
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

		var $statusText = $('#wpshadow-diagnostic-status-text');
		if ($statusText.length) {
			if (!enabled) {
				$statusText
					.text('<?php echo esc_js( __( 'Disabled', 'wpshadow' ) ); ?>')
					.attr('class', 'wps-status-text wps-status-text--muted');
			} else {
				$statusText
					.text($statusText.data('original') || '')
					.attr('class', String($statusText.data('original-class') || 'wps-status-text'));
			}
		}
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
		var className = String($select.data('class-name') || '');
		var nonce = String($select.data('nonce') || '');
		var frequency = String($select.val() || 'default');
		var $status = $('#wpshadow-diagnostic-action-status');

		if (!className || !nonce) {
			$status.text('<?php echo esc_js( __( 'Missing scheduling information. Please refresh and try again.', 'wpshadow' ) ); ?>');
			return;
		}

		$button.prop('disabled', true);
		$status.text('<?php echo esc_js( __( 'Saving frequency...', 'wpshadow' ) ); ?>');

		$.post(ajaxurl, {
			action: 'wpshadow_save_diagnostic_frequency',
			nonce: nonce,
			class_name: className,
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
		<div class="wps-card-body">
			<div class="wpshadow-diagnostic-status-filters wps-diagnostic-filter-bar">
				<label>
					<span class="wps-diagnostic-filter-label"><?php esc_html_e( 'Search', 'wpshadow' ); ?></span>
					<input type="search" id="wpshadow-diag-filter-search" class="regular-text" placeholder="<?php echo esc_attr__( 'Diagnostic name...', 'wpshadow' ); ?>" />
				</label>
				<label>
					<span class="wps-diagnostic-filter-label"><?php esc_html_e( 'Result', 'wpshadow' ); ?></span>
					<select id="wpshadow-diag-filter-result">
						<option value="all"><?php esc_html_e( 'All Results', 'wpshadow' ); ?></option>
						<option value="failed"><?php esc_html_e( 'Failed', 'wpshadow' ); ?></option>
						<option value="passed"><?php esc_html_e( 'Passed', 'wpshadow' ); ?></option>
						<option value="disabled"><?php esc_html_e( 'Disabled', 'wpshadow' ); ?></option>
						<option value="unknown"><?php esc_html_e( 'Unknown', 'wpshadow' ); ?></option>
					</select>
				</label>
				<label>
					<span class="wps-diagnostic-filter-label"><?php esc_html_e( 'Family', 'wpshadow' ); ?></span>
					<select id="wpshadow-diag-filter-family">
						<option value="all"><?php esc_html_e( 'All Families', 'wpshadow' ); ?></option>
						<?php foreach ( $family_options as $family_key => $family_label ) : ?>
							<option value="<?php echo esc_attr( $family_key ); ?>"><?php echo esc_html( $family_label ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>
					<span class="wps-diagnostic-filter-label"><?php esc_html_e( 'Confidence', 'wpshadow' ); ?></span>
					<select id="wpshadow-diag-filter-confidence">
						<option value="all"><?php esc_html_e( 'All Tiers', 'wpshadow' ); ?></option>
						<option value="high"><?php esc_html_e( 'High', 'wpshadow' ); ?></option>
						<option value="standard"><?php esc_html_e( 'Standard', 'wpshadow' ); ?></option>
						<option value="low"><?php esc_html_e( 'Low', 'wpshadow' ); ?></option>
					</select>
				</label>
				<label>
					<input type="checkbox" id="wpshadow-diag-filter-core-only" />
					<?php esc_html_e( 'Core Checks Only', 'wpshadow' ); ?>
				</label>
				<button type="button" id="wpshadow-diag-filter-clear" class="button button-secondary"><?php esc_html_e( 'Clear Filters', 'wpshadow' ); ?></button>
			</div>
			<div class="wpshadow-diagnostic-status-table-wrap wps-diagnostic-table-wrap">
				<table id="wpshadow-diagnostic-status-table" class="widefat striped wps-diagnostic-table">
					<thead>
						<tr>
							<th class="wps-diagnostic-col-index" aria-sort="none">
								<button type="button" class="wpshadow-sort-btn wps-sort-button-reset" data-sort-key="index" data-sort-type="number">
									<?php esc_html_e( '#', 'wpshadow' ); ?>
								</button>
							</th>
							<th aria-sort="none">
								<button type="button" class="wpshadow-sort-btn wps-sort-button-reset" data-sort-key="name" data-sort-type="text">
									<?php esc_html_e( 'Diagnostic', 'wpshadow' ); ?>
								</button>
							</th>
							<th aria-sort="none">
								<button type="button" class="wpshadow-sort-btn wps-sort-button-reset" data-sort-key="last-run" data-sort-type="number">
									<?php esc_html_e( 'Last Run', 'wpshadow' ); ?>
								</button>
							</th>
							<th aria-sort="none">
								<button type="button" class="wpshadow-sort-btn wps-sort-button-reset" data-sort-key="next-run" data-sort-type="number">
									<?php esc_html_e( 'Next Run', 'wpshadow' ); ?>
								</button>
							</th>
							<th aria-sort="none">
								<button type="button" class="wpshadow-sort-btn wps-sort-button-reset" data-sort-key="result" data-sort-type="number">
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
							<tr data-sort-index="<?php echo esc_attr( (string) ( (int) $index + 1 ) ); ?>" data-sort-name="<?php echo esc_attr( strtolower( (string) $row['name'] ) ); ?>" data-sort-last-run="<?php echo esc_attr( (string) (int) ( $row['last_run_ts'] ?? 0 ) ); ?>" data-sort-next-run="<?php echo esc_attr( (string) (int) ( $row['next_run_sort'] ?? 0 ) ); ?>" data-sort-result="<?php echo esc_attr( (string) $status_order ); ?>" data-filter-name="<?php echo esc_attr( strtolower( (string) $row['name'] ) ); ?>" data-filter-result="<?php echo esc_attr( $r_raw ); ?>" data-filter-family="<?php echo esc_attr( sanitize_key( (string) ( $row['family'] ?? '' ) ) ); ?>" data-filter-enabled="<?php echo esc_attr( ! empty( $row['enabled'] ) ? 'enabled' : 'disabled' ); ?>" data-filter-confidence="<?php echo esc_attr( (string) ( $row['confidence'] ?? 'standard' ) ); ?>" data-filter-core="<?php echo esc_attr( ! empty( $row['is_core'] ) ? 'yes' : 'no' ); ?>">
								<td class="wpshadow-col-index"><?php echo esc_html( (string) ( (int) $index + 1 ) ); ?></td>
								<td data-sort-value="<?php echo esc_attr( strtolower( (string) $row['name'] ) ); ?>">
									<a href="<?php echo esc_url( (string) $row['detail_url'] ); ?>">
										<?php echo esc_html( (string) $row['name'] ); ?>
									</a>
									<div class="wps-badge-row">
										<?php
										$is_core = ! empty( $row['is_core'] );
										$confidence = (string) ( $row['confidence'] ?? 'standard' );
										?>
										<?php if ( $is_core ) : ?>
											<span class="wps-core-badge wps-core-badge--compact">
												<?php esc_html_e( 'Core', 'wpshadow' ); ?>
											</span>
										<?php endif; ?>
										<span class="wps-confidence-badge wps-confidence-badge--compact wps-confidence-badge--<?php echo esc_attr( $confidence ); ?>">
											<?php echo esc_html( ucfirst( $confidence ) ); ?>
										</span>
									</div>
								</td>
								<td data-sort-value="<?php echo esc_attr( (string) (int) ( $row['last_run_ts'] ?? 0 ) ); ?>"><?php echo wp_kses_post( (string) $row['last_run'] ); ?></td>
								<td data-sort-value="<?php echo esc_attr( (string) (int) ( $row['next_run_sort'] ?? 0 ) ); ?>"><?php echo wp_kses_post( (string) $row['next_run'] ); ?></td>
								<td><?php
							$status_class = '';
							if ( 'passed' === $r_raw ) {
								$status_class = 'wps-status-text wps-status-text--passed';
							} elseif ( 'failed' === $r_raw ) {
								$status_class = 'wps-status-text wps-status-text--failed';
							} elseif ( 'disabled' === $r_raw ) {
								$status_class = 'wps-status-text wps-status-text--muted';
							}

							if ( '' !== $status_class ) {
								echo '<span class="' . esc_attr( $status_class ) . '">' . esc_html( (string) $row['status'] ) . '</span>';
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

		<?php wpshadow_render_diagnostic_action_handlers_js(); ?>

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
			var $confidenceFilter = $('#wpshadow-diag-filter-confidence');
			var $coreOnlyFilter = $('#wpshadow-diag-filter-core-only');
			var filterDebounceTimer = null;

			var rows = $tbody.find('tr').map(function() {
				var rowEl = this;
				return {
					el: rowEl,
					name: String(rowEl.getAttribute('data-filter-name') || ''),
					result: String(rowEl.getAttribute('data-filter-result') || 'unknown'),
					family: String(rowEl.getAttribute('data-filter-family') || ''),
					confidence: String(rowEl.getAttribute('data-filter-confidence') || 'standard'),
					isCore: String(rowEl.getAttribute('data-filter-core') || 'no') === 'yes',
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
				var confidence = String($confidenceFilter.val() || 'all');
				var coreOnly = $coreOnlyFilter.is(':checked');

				$.each(rows, function(_, row) {
					if (!row || !row.el) {
						return;
					}

					var matchesSearch = !search || row.name.indexOf(search) !== -1;
					var matchesResult = ('all' === result) || row.result === result;
					var matchesFamily = ('all' === family) || row.family === family;
					var matchesConfidence = ('all' === confidence) || row.confidence === confidence;
					var matchesCore = !coreOnly || row.isCore;

					row.el.hidden = !(matchesSearch && matchesResult && matchesFamily && matchesConfidence && matchesCore);
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
				$button.append('<span class="wpshadow-sort-indicator" aria-hidden="true">' + (direction === 'asc' ? '▲' : '▼') + '</span>');
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

			$(document).on('change', '#wpshadow-diag-filter-result, #wpshadow-diag-filter-family, #wpshadow-diag-filter-confidence', function() {
				applyFiltersNow();
			});

			$(document).on('change', '#wpshadow-diag-filter-core-only', function() {
				applyFiltersNow();
			});

			$(document).on('click', '#wpshadow-diag-filter-clear', function(event) {
				event.preventDefault();
				$searchFilter.val('');
				$resultFilter.val('all');
				$familyFilter.val('all');
				$confidenceFilter.val('all');
				$coreOnlyFilter.prop('checked', false);
				applyFiltersNow();
			});

			applyFiltersNow();
		})();

		var runAllTestsPollTimer = null;
		var getRunAllNonce = function () {
			return (typeof wpshadowDashboardData !== 'undefined' && wpshadowDashboardData.scan_nonce)
				? wpshadowDashboardData.scan_nonce
				: '';
		};

		var updateReadinessSummary = function () {
			var nonce = getRunAllNonce();
			var $summary = $('#wpshadow-readiness-summary');

			if (!nonce || !$summary.length) {
				return;
			}

			$.post(ajaxurl, {
				action: 'wpshadow_readiness_inventory',
				nonce: nonce
			}).done(function (response) {
				if (!response || !response.success || !response.data || !response.data.summary) {
					return;
				}

				var diagnostics = response.data.summary.diagnostics || {};
				var treatments = response.data.summary.treatments || {};

				var dProd = parseInt(diagnostics.production || 0, 10);
				var dBeta = parseInt(diagnostics.beta || 0, 10);
				var dPlan = parseInt(diagnostics.planned || 0, 10);
				var tProd = parseInt(treatments.production || 0, 10);
				var tBeta = parseInt(treatments.beta || 0, 10);
				var tPlan = parseInt(treatments.planned || 0, 10);

				$summary.html(
					'<strong><?php echo esc_js( __( 'Lifecycle Readiness', 'wpshadow' ) ); ?></strong><br>' +
					'<?php echo esc_js( __( 'Diagnostics', 'wpshadow' ) ); ?>: ' + dProd + ' <?php echo esc_js( __( 'production', 'wpshadow' ) ); ?>, ' + dBeta + ' <?php echo esc_js( __( 'beta', 'wpshadow' ) ); ?>, ' + dPlan + ' <?php echo esc_js( __( 'planned', 'wpshadow' ) ); ?><br>' +
					'<?php echo esc_js( __( 'Treatments', 'wpshadow' ) ); ?>: ' + tProd + ' <?php echo esc_js( __( 'production', 'wpshadow' ) ); ?>, ' + tBeta + ' <?php echo esc_js( __( 'beta', 'wpshadow' ) ); ?>, ' + tPlan + ' <?php echo esc_js( __( 'planned', 'wpshadow' ) ); ?>'
				);
			});
		};

		var updateRunAllProgress = function (percent) {
			var safePercent = Math.max(0, Math.min(100, parseInt(percent, 10) || 0));
			var $wrap = $('#wpshadow-run-all-tests-progress-wrap');
			var $bar = $('#wpshadow-run-all-tests-progress-bar');
			var $text = $('#wpshadow-run-all-tests-progress-text');
			var $track = $wrap.find('.wps-run-tests-progress-track');

			$wrap.prop('hidden', false);
			$bar.css('width', safePercent + '%');
			$text.text(safePercent + '%');
			$track.attr('aria-valuenow', safePercent);
		};

		var stopRunAllPolling = function () {
			if (runAllTestsPollTimer) {
				clearInterval(runAllTestsPollTimer);
				runAllTestsPollTimer = null;
			}
		};

		var pollRunAllStatus = function ($btn, $status, nonce) {
			$.post(ajaxurl, {
				action: 'wpshadow_deep_scan_status',
				nonce: nonce
			}).done(function (response) {
				if (!response || !response.success || !response.data) {
					return;
				}

				var data = response.data;
				if (data.running) {
					updateRunAllProgress(data.progress_percent);
					$status.text('<?php echo esc_js( __( 'A scan is already running. Tracking progress …', 'wpshadow' ) ); ?>');
					$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Running…', 'wpshadow' ) ); ?>');
					return;
				}

				updateRunAllProgress(100);
				$status.text('<?php echo esc_js( __( 'Scan complete. Refreshing…', 'wpshadow' ) ); ?>');
				stopRunAllPolling();
				setTimeout(function () { window.location.reload(); }, 800);
			});
		};

		var startRunAllStatusPolling = function ($btn, $status, nonce) {
			stopRunAllPolling();
			pollRunAllStatus($btn, $status, nonce);
			runAllTestsPollTimer = setInterval(function () {
				pollRunAllStatus($btn, $status, nonce);
			}, 3000);
		};

		(function initRunAllTestsProgressFromCurrentState() {
			var nonce = getRunAllNonce();
			if (!nonce) {
				return;
			}

			updateReadinessSummary();

			var $btn = $('#wpshadow-run-all-tests-btn');
			var $status = $('#wpshadow-run-all-tests-status');

			$.post(ajaxurl, {
				action: 'wpshadow_deep_scan_status',
				nonce: nonce
			}).done(function (response) {
				if (response && response.success && response.data && response.data.running) {
					updateRunAllProgress(response.data.progress_percent);
					$status.text('<?php echo esc_js( __( 'A scan is already running. Tracking progress …', 'wpshadow' ) ); ?>');
					$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Running…', 'wpshadow' ) ); ?>');
					startRunAllStatusPolling($btn, $status, nonce);
				}
			});
		})();

		$(document).on('click', '#wpshadow-run-all-tests-btn', function() {
			var $btn    = $(this);
			var $status = $('#wpshadow-run-all-tests-status');
			var nonce   = getRunAllNonce();

			if (!nonce) {
				$status.text('<?php echo esc_js( __( 'Could not start scan: missing security token.', 'wpshadow' ) ); ?>');
				return;
			}

			$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Running…', 'wpshadow' ) ); ?>');
			$status.text('<?php echo esc_js( __( 'Running all tests, please wait…', 'wpshadow' ) ); ?>');
			updateRunAllProgress(3);

			$.post(ajaxurl, {
				action: 'wpshadow_deep_scan',
				nonce: nonce,
				mode: 'now'
			}).done(function(response) {
				var payload = response;
				var parseResponseText = function(raw) {
					if (typeof raw !== 'string') {
						return raw;
					}

					var trimmed = raw.trim();
					if (!trimmed) {
						return raw;
					}

					if ('-1' === trimmed) {
						return { success: false, data: { message: '<?php echo esc_js( __( 'Security token expired. Reload the page and try again.', 'wpshadow' ) ); ?>' } };
					}

					try {
						return JSON.parse(trimmed);
					} catch (e) {
						var start = trimmed.indexOf('{');
						var end = trimmed.lastIndexOf('}');
						if (start !== -1 && end !== -1 && end > start) {
							try {
								return JSON.parse(trimmed.substring(start, end + 1));
							} catch (innerErr) {
								return raw;
							}
						}
						return raw;
					}
				};

				var extractMessage = function(p) {
					if (!p) {
						return '';
					}
					if (p.data && p.data.message) {
						return p.data.message;
					}
					if (p.data && p.data.data && p.data.data.message) {
						return p.data.data.message;
					}
					if (p.message) {
						return p.message;
					}
					return '';
				};

				payload = parseResponseText(payload);

				if (payload && payload.success && payload.data && payload.data.success === false) {
					if (payload.data.locked) {
						$status.text(extractMessage(payload) || '<?php echo esc_js( __( 'A scan is already running. Tracking progress …', 'wpshadow' ) ); ?>');
						startRunAllStatusPolling($btn, $status, nonce);
					} else {
						$status.text(extractMessage(payload) || '<?php echo esc_js( __( 'Scan failed. Please try again.', 'wpshadow' ) ); ?>');
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Run All Tests', 'wpshadow' ) ); ?>');
					}
				} else if (payload && payload.success) {
					updateRunAllProgress(100);
					$status.text('<?php echo esc_js( __( 'All tests complete. Refreshing…', 'wpshadow' ) ); ?>');
					stopRunAllPolling();
					setTimeout(function() { window.location.reload(); }, 800);
				} else {
					var msg = extractMessage(payload)
						? extractMessage(payload)
						: '<?php echo esc_js( __( 'Scan failed. Please try again.', 'wpshadow' ) ); ?>';
					$status.text(msg);
					stopRunAllPolling();
					$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Run All Tests', 'wpshadow' ) ); ?>');
				}
			}).fail(function() {
				$status.text('<?php echo esc_js( __( 'Scan request failed. Please try again.', 'wpshadow' ) ); ?>');
				stopRunAllPolling();
				$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Run All Tests', 'wpshadow' ) ); ?>');
			});
		});

		});
	</script>
	<?php
}


// Load new dashboard page redesign (replaces old gauges/modal system)
require_once WPSHADOW_PATH . "includes/ui/views/dashboard-page-v2.php";

