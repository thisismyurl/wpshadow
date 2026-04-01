<?php
/**
 * Treatment Sandbox Validator
 *
 * Tests treatments in isolation before applying to production environment.
 * Prevents dangerous treatments from causing site damage by validating code
 * safety, checking for destructive operations, and dry-run simulation.
 *
 * **Safety Checks:**
 * - Static code analysis for dangerous functions
 * - Dry-run execution to predict impact
 * - Backup verification before application
 * - Rollback availability confirmation
 * - Permission scope validation
 *
 * **Philosophy Alignment:**
 * - #8 (Inspire Confidence): Users trust treatments won't break their site
 * - #1 (Helpful Neighbor): Clear explanation of what treatment will do
 * - #10 (Beyond Pure): Proactive safety validation
 *
 * @package    WPShadow
 * @subpackage Core
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment Sandbox Class
 *
 * Validates treatment safety before execution.
 *
 * @since 0.6093.1200
 */
class Treatment_Sandbox {

	/**
	 * Dangerous functions that treatments should not use
	 *
	 * @var array<string>
	 */
	private static $dangerous_functions = array(
		'eval',
		'exec',
		'system',
		'shell_exec',
		'passthru',
		'proc_open',
		'popen',
		'unlink',          // Direct file deletion
		'rmdir',           // Direct directory deletion
		'file_put_contents', // Prefer WP Filesystem API
		'fwrite',          // Prefer WP Filesystem API
	);

	/**
	 * Sensitive files that require extra caution
	 *
	 * @var array<string>
	 */
	private static $sensitive_files = array(
		'wp-config.php',
		'.htaccess',
		'php.ini',
		'.user.ini',
		'web.config',
	);

	/**
	 * Validate treatment safety before execution.
	 *
	 * @since 0.6093.1200
	 * @param  string $treatment_class Fully qualified treatment class name.
	 * @return array {
	 *     Safety validation result.
	 *
	 *     @type bool   $safe            Whether treatment is safe to apply.
	 *     @type array  $warnings        Safety warnings.
	 *     @type array  $critical_issues Critical safety issues.
	 *     @type array  $predicted_changes What treatment will modify.
	 * }
	 */
	public static function validate_treatment( string $treatment_class ): array {
		$warnings        = array();
		$critical_issues = array();
		$predicted_changes = array();

		// Verify class exists and is a treatment
		if ( ! class_exists( $treatment_class ) ) {
			$critical_issues[] = sprintf(
				/* translators: %s: class name */
				__( 'Treatment class %s does not exist', 'wpshadow' ),
				$treatment_class
			);
			return array(
				'safe'              => false,
				'warnings'          => $warnings,
				'critical_issues'   => $critical_issues,
				'predicted_changes' => $predicted_changes,
			);
		}

		// Check if extends Treatment_Base
		if ( ! is_subclass_of( $treatment_class, 'WPShadow\Core\Treatment_Base' ) ) {
			$critical_issues[] = __( 'Treatment must extend Treatment_Base', 'wpshadow' );
		}

		// Perform static code analysis
		$code_issues = self::analyze_treatment_code( $treatment_class );
		if ( ! empty( $code_issues['dangerous'] ) ) {
			$critical_issues = array_merge( $critical_issues, $code_issues['dangerous'] );
		}
		if ( ! empty( $code_issues['warnings'] ) ) {
			$warnings = array_merge( $warnings, $code_issues['warnings'] );
		}

		// Check for backup capability
		if ( ! method_exists( $treatment_class, 'create_backup' ) ) {
			$warnings[] = __( 'Treatment does not implement backup functionality', 'wpshadow' );
		}

		// Check for rollback capability
		if ( ! method_exists( $treatment_class, 'rollback' ) ) {
			$warnings[] = __( 'Treatment does not implement rollback functionality', 'wpshadow' );
		}

		// Run dry-run to predict changes
		if ( method_exists( $treatment_class, 'execute' ) ) {
			try {
				$dry_run_result = $treatment_class::execute( true );
				if ( isset( $dry_run_result['changes'] ) ) {
					$predicted_changes = $dry_run_result['changes'];
				}
			} catch ( \Exception $e ) {
				$critical_issues[] = sprintf(
					/* translators: %s: error message */
					__( 'Dry-run failed: %s', 'wpshadow' ),
					$e->getMessage()
				);
			}
		}

		$safe = empty( $critical_issues );

		return array(
			'safe'              => $safe,
			'warnings'          => $warnings,
			'critical_issues'   => $critical_issues,
			'predicted_changes' => $predicted_changes,
		);
	}

	/**
	 * Analyze treatment source code for dangerous patterns.
	 *
	 * @since 0.6093.1200
	 * @param  string $treatment_class Treatment class name.
	 * @return array {
	 *     Code analysis result.
	 *
	 *     @type array $dangerous Critical security issues.
	 *     @type array $warnings  Safety warnings.
	 * }
	 */
	private static function analyze_treatment_code( string $treatment_class ): array {
		$dangerous = array();
		$warnings  = array();

		try {
			$reflection = new \ReflectionClass( $treatment_class );
			$filename   = $reflection->getFileName();

			if ( false === $filename || ! is_readable( $filename ) ) {
				return array(
					'dangerous' => array( __( 'Cannot read treatment source file', 'wpshadow' ) ),
					'warnings'  => array(),
				);
			}

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$source = file_get_contents( $filename );

			// Check for dangerous functions
			foreach ( self::$dangerous_functions as $function ) {
				if ( preg_match( '/\b' . preg_quote( $function, '/' ) . '\s*\(/i', $source ) ) {
					$dangerous[] = sprintf(
						/* translators: %s: function name */
						__( 'Uses dangerous function: %s()', 'wpshadow' ),
						$function
					);
				}
			}

			// Check for sensitive file modifications
			foreach ( self::$sensitive_files as $file ) {
				if ( false !== stripos( $source, $file ) ) {
					$warnings[] = sprintf(
						/* translators: %s: file name */
						__( 'Modifies sensitive file: %s', 'wpshadow' ),
						$file
					);
				}
			}

			// Check for direct SQL queries
			if ( preg_match( '/\$wpdb->query\s*\(\s*["\'](?!.*prepare)/i', $source ) ) {
				$warnings[] = __( 'Contains unprepared SQL queries', 'wpshadow' );
			}

			// Check for nonce/capability verification in apply() method
			if ( $reflection->hasMethod( 'apply' ) ) {
				$apply_method = $reflection->getMethod( 'apply' );
				$start_line   = $apply_method->getStartLine();
				$end_line     = $apply_method->getEndLine();

				$lines = explode( "\n", $source );
				$apply_code = implode( "\n", array_slice( $lines, $start_line - 1, $end_line - $start_line + 1 ) );

				// Apply method should not do capability checks (handled by base class)
				// But warn if it modifies critical files
				if ( preg_match( '/wp-config\.php|\.htaccess/i', $apply_code ) ) {
					$warnings[] = __( 'Apply method modifies critical WordPress configuration files', 'wpshadow' );
				}
			}
		} catch ( \ReflectionException $e ) {
			$dangerous[] = sprintf(
				/* translators: %s: error message */
				__( 'Code analysis failed: %s', 'wpshadow' ),
				$e->getMessage()
			);
		}

		return array(
			'dangerous' => $dangerous,
			'warnings'  => $warnings,
		);
	}

	/**
	 * Get user-friendly safety report.
	 *
	 * Philosophy #1 (Helpful Neighbor): Explain risks in plain language.
	 *
	 * @since 0.6093.1200
	 * @param  array $validation_result Result from validate_treatment().
	 * @return string HTML safety report.
	 */
	public static function get_safety_report( array $validation_result ): string {
		if ( $validation_result['safe'] && empty( $validation_result['warnings'] ) ) {
			$report = '<div class="notice notice-success">';
			$report .= '<p><strong>' . esc_html__( '✅ Treatment Validated', 'wpshadow' ) . '</strong></p>';
			$report .= '<p>' . esc_html__( 'This treatment has passed all safety checks and is ready to apply.', 'wpshadow' ) . '</p>';

			if ( ! empty( $validation_result['predicted_changes'] ) ) {
				$report .= '<p><strong>' . esc_html__( 'Predicted Changes:', 'wpshadow' ) . '</strong></p>';
				$report .= '<ul>';
				foreach ( $validation_result['predicted_changes'] as $change ) {
					$report .= '<li>' . esc_html( $change ) . '</li>';
				}
				$report .= '</ul>';
			}

			$report .= '</div>';
			return $report;
		}

		$report = '<div class="notice notice-';
		$report .= ! empty( $validation_result['critical_issues'] ) ? 'error' : 'warning';
		$report .= '">';

		if ( ! empty( $validation_result['critical_issues'] ) ) {
			$report .= '<p><strong>' . esc_html__( '🚫 Critical Safety Issues', 'wpshadow' ) . '</strong></p>';
			$report .= '<ul>';
			foreach ( $validation_result['critical_issues'] as $issue ) {
				$report .= '<li>' . esc_html( $issue ) . '</li>';
			}
			$report .= '</ul>';
			$report .= '<p><strong>' . esc_html__( 'This treatment cannot be applied safely.', 'wpshadow' ) . '</strong></p>';
		}

		if ( ! empty( $validation_result['warnings'] ) ) {
			$report .= '<p><strong>' . esc_html__( '⚠️ Safety Warnings', 'wpshadow' ) . '</strong></p>';
			$report .= '<ul>';
			foreach ( $validation_result['warnings'] as $warning ) {
				$report .= '<li>' . esc_html( $warning ) . '</li>';
			}
			$report .= '</ul>';
			$report .= '<p>' . esc_html__( 'Review these warnings before proceeding. A backup will be created automatically.', 'wpshadow' ) . '</p>';
		}

		$report .= '</div>';
		return $report;
	}

	/**
	 * Validate treatment before application (hook integration).
	 *
	 * @since 0.6093.1200
	 * @param  string $treatment_class Treatment class to validate.
	 * @return bool True if safe to proceed, false otherwise.
	 */
	public static function pre_treatment_validation( string $treatment_class ): bool {
		$validation = self::validate_treatment( $treatment_class );

		// Log validation result
		if ( class_exists( 'WPShadow\Core\Activity_Logger' ) ) {
			Activity_Logger::log(
				'security_treatment_validation',
				array(
					'treatment'       => $treatment_class,
					'safe'            => $validation['safe'],
					'warnings'        => count( $validation['warnings'] ),
					'critical_issues' => count( $validation['critical_issues'] ),
				)
			);
		}

		/**
		 * Filter whether to allow treatment despite validation warnings.
		 *
		 * @since 0.6093.1200
		 *
		 * @param bool   $allow            Whether to allow treatment.
		 * @param array  $validation       Validation result.
		 * @param string $treatment_class  Treatment class.
		 */
		return apply_filters( 'wpshadow_allow_treatment_execution', $validation['safe'], $validation, $treatment_class );
	}
}
