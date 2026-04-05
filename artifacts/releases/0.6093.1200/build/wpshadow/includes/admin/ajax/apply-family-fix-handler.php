<?php
/**
 * Apply Family Fix AJAX Handler
 *
 * Applies treatments for a finding and optionally all family members
 * (Philosophy #9: Show Value by tracking grouped fixes efficiently)
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Apply_Family_Fix_Handler extends AJAX_Handler_Base {
	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_apply_family_fix', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle request to apply fix to finding and optionally family members
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_findings', 'manage_options', 'nonce' );

		$finding_id     = self::get_post_param( 'finding_id', 'text', '', true );
		$fix_all_family = self::get_post_param( 'fix_all_family', 'boolean', false );
		$family_ids     = self::get_post_array_param( 'family_ids', 'text', array() );

		// Always include the primary finding
		$finding_ids_to_fix = array( $finding_id );

		// Add family members if requested
		if ( $fix_all_family && ! empty( $family_ids ) ) {
			$finding_ids_to_fix = array_merge( $finding_ids_to_fix, $family_ids );
		}

		$finding_ids_to_fix = array_values(
			array_unique(
				array_filter(
					array_map( 'strval', $finding_ids_to_fix )
				)
			)
		);

		// Apply fixes for each finding
		$results          = array();
		$total_time_saved = 0;

		foreach ( $finding_ids_to_fix as $fid ) {
			$result = \wpshadow_attempt_autofix( (string) $fid );

			if ( is_array( $result ) && ! empty( $result['success'] ) ) {
				$results[ $fid ] = array(
					'success' => true,
					'message' => $result['message'] ?? __( 'Fixed', 'wpshadow' ),
				);

				// Log the fix
				\WPShadow\Core\Activity_Logger::log(
					'treatment_applied',
					sprintf( 'Auto-fix applied: %s', (string) $fid ),
					'workflows',
					array(
						'finding_id' => (string) $fid,
						'message'    => $result['message'] ?? '',
					)
				);

				// Track KPI (Philosophy #9)
				$time_saved = $result['time_saved'] ?? 0;
				if ( $time_saved > 0 ) {
					$total_time_saved += $time_saved;
				}
			} else {
				$results[ $fid ] = array(
					'success' => false,
					'message' => $result['message'] ?? __( 'Auto-fix failed', 'wpshadow' ),
				);
			}
		}

		// Log family-grouped fix (Philosophy #9: Show Value)
		$successful_count = count( array_filter( $results, fn( $r ) => $r['success'] ) );

		if ( $fix_all_family && $successful_count > 1 ) {
			// Log that this was a family-grouped fix
			$family_label = self::get_post_param( 'family_label', 'text', 'family' );

			\WPShadow\Core\Activity_Logger::log(
				'family_grouped_fix',
				"Fixed {$successful_count} issues in {$family_label}",
				'',
				array(
					'primary_finding' => $finding_id,
					'family_ids'      => $finding_ids_to_fix,
					'count'           => $successful_count,
					'time_saved'      => $total_time_saved,
				)
			);
		}

		// Send response
		if ( $successful_count === count( $finding_ids_to_fix ) ) {
			self::send_success(
				array(
					'message'           => sprintf(
						_n( 'Fixed %d issue', 'Fixed %d issues', $successful_count, 'wpshadow' ),
						$successful_count
					),
					'fixes'             => $results,
					'successful_count'  => $successful_count,
					'total_count'       => count( $finding_ids_to_fix ),
					'time_saved'        => $total_time_saved,
					'is_family_grouped' => $fix_all_family && count( $finding_ids_to_fix ) > 1,
				)
			);
		} else {
			self::send_error(
				sprintf(
					__( 'Fixed %1$d of %2$d issues', 'wpshadow' ),
					$successful_count,
					count( $finding_ids_to_fix )
				),
				array(
					'fixes'            => $results,
					'successful_count' => $successful_count,
					'total_count'      => count( $finding_ids_to_fix ),
				)
			);
		}
	}
}
