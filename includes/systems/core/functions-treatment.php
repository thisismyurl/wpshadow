<?php
/**
 * Treatment Helper Functions
 *
 * Global convenience wrappers around the treatment registry so UI and AJAX
 * handlers can trigger fixes without duplicating registry and policy checks.
 *
 * @package WPShadow
 */

declare(strict_types=1);

use WPShadow\Treatments\Treatment_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpshadow_get_treatment' ) ) {
	/**
	 * Resolve the treatment class name for a finding.
	 *
	 * @param string $finding_id Finding/treatment slug.
	 * @return string|null
	 */
	function wpshadow_get_treatment( string $finding_id ): ?string {
		if ( '' === trim( $finding_id ) || ! class_exists( Treatment_Registry::class ) ) {
			return null;
		}

		$finding_id       = sanitize_key( $finding_id );
		$treatment_class = Treatment_Registry::get_treatment( $finding_id );

		/**
		 * Filter the treatment class resolved for a finding.
		 *
		 * @param string|null $treatment_class Fully-qualified treatment class or null.
		 * @param string      $finding_id      Sanitized finding identifier.
		 */
		$treatment_class = apply_filters( 'wpshadow_treatment_class', $treatment_class, $finding_id );

		return is_string( $treatment_class ) && '' !== $treatment_class ? $treatment_class : null;
	}
}

if ( ! function_exists( 'wpshadow_is_treatment_enabled' ) ) {
	/**
	 * Determine whether a treatment is enabled for manual/automatic use.
	 *
	 * @param string $finding_id Finding/treatment slug.
	 * @return bool
	 */
	function wpshadow_is_treatment_enabled( string $finding_id ): bool {
		$treatment_class = wpshadow_get_treatment( $finding_id );
		if ( ! is_string( $treatment_class ) || '' === $treatment_class ) {
			return false;
		}

		$disabled = get_option( 'wpshadow_disabled_treatment_classes', array() );
		if ( ! is_array( $disabled ) ) {
			$disabled = array();
		}

		$enabled = ! in_array( $treatment_class, $disabled, true );
		return (bool) apply_filters( 'wpshadow_treatment_enabled', $enabled, $treatment_class );
	}
}

if ( ! function_exists( 'wpshadow_can_apply_treatment' ) ) {
	/**
	 * Determine whether a treatment is currently available and executable.
	 *
	 * @param string $finding_id Finding/treatment slug.
	 * @return bool
	 */
	function wpshadow_can_apply_treatment( string $finding_id ): bool {
		$treatment_class = wpshadow_get_treatment( $finding_id );
		if ( ! is_string( $treatment_class ) || '' === $treatment_class || ! class_exists( $treatment_class ) ) {
			return false;
		}

		if ( ! wpshadow_is_treatment_enabled( $finding_id ) ) {
			return false;
		}

		if ( method_exists( $treatment_class, 'can_apply' ) && ! $treatment_class::can_apply() ) {
			return false;
		}

		$can_apply = method_exists( $treatment_class, 'execute' );

		/**
		 * Filter whether a treatment is currently executable for a finding.
		 *
		 * @param bool   $can_apply       Whether the treatment can be applied.
		 * @param string $finding_id      Finding identifier.
		 * @param string $treatment_class Fully-qualified treatment class name.
		 */
		return (bool) apply_filters( 'wpshadow_can_apply_treatment', $can_apply, $finding_id, $treatment_class );
	}
}

if ( ! function_exists( 'wpshadow_attempt_autofix' ) ) {
	/**
	 * Execute the treatment mapped to a finding.
	 *
	 * @param string $finding_id Finding/treatment slug.
	 * @param bool   $dry_run    Optional dry-run mode.
	 * @return array<string,mixed>
	 */
	function wpshadow_attempt_autofix( string $finding_id, bool $dry_run = false ): array {
		$finding_id = sanitize_key( $finding_id );
		if ( '' === $finding_id ) {
			return array(
				'success' => false,
				'message' => __( 'No finding was supplied for this fix.', 'wpshadow' ),
			);
		}

		/**
		 * Filter a top-level treatment attempt before WPShadow applies its default flow.
		 *
		 * Return a result array to short-circuit the built-in treatment resolution.
		 *
		 * @param array<string,mixed>|null $pre_result Precomputed result or null to continue.
		 * @param string                   $finding_id Finding identifier.
		 * @param bool                     $dry_run    Whether the run is a dry run.
		 */
		$pre_result = apply_filters( 'wpshadow_pre_attempt_autofix', null, $finding_id, $dry_run );
		if ( is_array( $pre_result ) ) {
			/**
			 * Filter the final top-level treatment result.
			 *
			 * @param array<string,mixed> $result     Result payload.
			 * @param string              $finding_id Finding identifier.
			 * @param bool                $dry_run    Whether the run is a dry run.
			 */
			return apply_filters( 'wpshadow_attempt_autofix_result', $pre_result, $finding_id, $dry_run );
		}

		/**
		 * Fires before WPShadow begins its top-level treatment attempt flow.
		 *
		 * @param string $finding_id Finding identifier.
		 * @param bool   $dry_run    Whether the run is a dry run.
		 */
		do_action( 'wpshadow_before_attempt_autofix', $finding_id, $dry_run );

		$result = null;
		if ( ! class_exists( Treatment_Registry::class ) ) {
			$result = array(
				'success' => false,
				'message' => __( 'Treatment registry is not available in this environment.', 'wpshadow' ),
			);
		} elseif ( ! wpshadow_can_apply_treatment( $finding_id ) ) {
			$result = array(
				'success' => false,
				'message' => __( 'This fix is not currently available.', 'wpshadow' ),
			);
		} else {
			$treatment_class = wpshadow_get_treatment( $finding_id );
			if ( is_string( $treatment_class ) && '' !== $treatment_class && ! $dry_run && class_exists( '\WPShadow\Admin\File_Write_Registry' ) ) {
				$file_write_classes = \WPShadow\Admin\File_Write_Registry::get_all();

				if ( in_array( $treatment_class, $file_write_classes, true ) ) {
					$result = array(
						'success'             => false,
						'file_write_review'   => true,
						'finding_id'          => $finding_id,
						'message'             => __( 'This fix writes to server files and must be reviewed from the File Review workflow before it can be applied.', 'wpshadow' ),
						'review_page_url'     => admin_url( 'admin.php?page=wpshadow-file-write-review' ),
					);
				}
			}
		}

		if ( ! is_array( $result ) ) {
			try {
				$result = Treatment_Registry::apply_treatment( $finding_id, $dry_run );
			} catch ( \Throwable $exception ) {
				$result = array(
					'success' => false,
					'message' => sprintf(
						/* translators: %s: exception message */
						__( 'Fix failed: %s', 'wpshadow' ),
						$exception->getMessage()
					),
				);
			}
		}

		/**
		 * Fires after WPShadow completes its top-level treatment attempt flow.
		 *
		 * @param string              $finding_id Finding identifier.
		 * @param bool                $dry_run    Whether the run was a dry run.
		 * @param array<string,mixed> $result     Result payload.
		 */
		do_action( 'wpshadow_after_attempt_autofix', $finding_id, $dry_run, $result );

		/**
		 * Filter the final top-level treatment result.
		 *
		 * @param array<string,mixed> $result     Result payload.
		 * @param string              $finding_id Finding identifier.
		 * @param bool                $dry_run    Whether the run is a dry run.
		 */
		return apply_filters( 'wpshadow_attempt_autofix_result', $result, $finding_id, $dry_run );
	}
}


