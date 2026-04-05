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

		return Treatment_Registry::get_treatment( sanitize_key( $finding_id ) );
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

		return method_exists( $treatment_class, 'execute' );
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

		if ( ! class_exists( Treatment_Registry::class ) ) {
			return array(
				'success' => false,
				'message' => __( 'Treatment registry is not available in this environment.', 'wpshadow' ),
			);
		}

		if ( ! wpshadow_can_apply_treatment( $finding_id ) ) {
			return array(
				'success' => false,
				'message' => __( 'This fix is not currently available.', 'wpshadow' ),
			);
		}

		$treatment_class = wpshadow_get_treatment( $finding_id );
		if ( is_string( $treatment_class ) && '' !== $treatment_class && ! $dry_run && class_exists( '\WPShadow\Admin\File_Write_Registry' ) ) {
			$file_write_classes = \WPShadow\Admin\File_Write_Registry::get_all();

			if ( in_array( $treatment_class, $file_write_classes, true ) ) {
				return array(
					'success'             => false,
					'file_write_review'   => true,
					'finding_id'          => $finding_id,
					'message'             => __( 'This fix writes to server files and must be reviewed from the File Review workflow before it can be applied.', 'wpshadow' ),
					'review_page_url'     => admin_url( 'admin.php?page=wpshadow-file-write-review' ),
				);
			}
		}

		try {
			return Treatment_Registry::apply_treatment( $finding_id, $dry_run );
		} catch ( \Throwable $exception ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: exception message */
					__( 'Fix failed: %s', 'wpshadow' ),
					$exception->getMessage()
				),
			);
		}
	}
}


