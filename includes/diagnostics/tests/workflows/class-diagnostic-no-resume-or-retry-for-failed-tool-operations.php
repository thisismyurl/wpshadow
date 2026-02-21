<?php
/**
 * No Resume or Retry for Failed Tool Operations
 *
 * Checks for resume/retry capability in failed tool operations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tools
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_No_Resume_Or_Retry_For_Failed_Tool_Operations Class
 *
 * Validates tool operation recovery capabilities.
 *
 * @since 1.6030.2148
 */
class Diagnostic_No_Resume_Or_Retry_For_Failed_Tool_Operations extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-resume-retry-failed-tools';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tool Operation Resume/Retry';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates resume and retry capability for failed tool operations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests tool operation recovery mechanisms.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check for operation checkpoints
		if ( ! self::has_operation_checkpoints() ) {
			$issues[] = __( 'No checkpoint system for operation recovery', 'wpshadow' );
		}

		// 2. Check for retry mechanism
		if ( ! self::has_retry_mechanism() ) {
			$issues[] = __( 'No automatic retry for transient failures', 'wpshadow' );
		}

		// 3. Check for resume capability
		if ( ! self::has_resume_capability() ) {
			$issues[] = __( 'Operations cannot resume from last checkpoint', 'wpshadow' );
		}

		// 4. Check for failure tracking
		if ( ! self::has_failure_tracking() ) {
			$issues[] = __( 'Failed operations not tracked for manual retry', 'wpshadow' );
		}

		// 5. Check for recovery UI
		if ( ! self::has_recovery_ui() ) {
			$issues[] = __( 'No UI to resume or retry failed operations', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( '%d recovery mechanism issues found', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/tool-operation-recovery',
				'recommendations' => array(
					__( 'Implement checkpoint system for long operations', 'wpshadow' ),
					__( 'Add automatic retry for transient failures', 'wpshadow' ),
					__( 'Store operation state for resuming', 'wpshadow' ),
					__( 'Track and display failed operations', 'wpshadow' ),
					__( 'Provide UI to retry or resume failed operations', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check for operation checkpoints.
	 *
	 * @since  1.6030.2148
	 * @return bool True if checkpoints implemented.
	 */
	private static function has_operation_checkpoints() {
		// Check if operations save progress
		if ( has_filter( 'wpshadow_operation_checkpoint' ) ) {
			return true;
		}

		// Check for checkpoint storage option
		$checkpoint = get_option( 'wpshadow_operation_checkpoint' );
		if ( ! empty( $checkpoint ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for retry mechanism.
	 *
	 * @since  1.6030.2148
	 * @return bool True if retry implemented.
	 */
	private static function has_retry_mechanism() {
		// Check for option-backed failed operations.
		if ( false !== get_option( 'wpshadow_failed_operations_log', false ) ) {
			return true;
		}

		// Check for retry hook
		if ( has_action( 'wpshadow_retry_failed_operation' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for resume capability.
	 *
	 * @since  1.6030.2148
	 * @return bool True if resume implemented.
	 */
	private static function has_resume_capability() {
		// Check for resume state storage
		if ( has_filter( 'wpshadow_operation_resume_state' ) ) {
			return true;
		}

		// Check for stored resume data
		$resume_data = get_option( 'wpshadow_resume_operation' );
		if ( ! empty( $resume_data ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for failure tracking.
	 *
	 * @since  1.6030.2148
	 * @return bool True if tracking implemented.
	 */
	private static function has_failure_tracking() {
		// Check for failure option
		$failures = get_option( 'wpshadow_failed_operations_log' );
		if ( ! empty( $failures ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for recovery UI.
	 *
	 * @since  1.6030.2148
	 * @return bool True if UI implemented.
	 */
	private static function has_recovery_ui() {
		// Check for admin screen showing failed operations
		if ( has_filter( 'wpshadow_show_failed_operations' ) ) {
			return true;
		}

		// Check for JavaScript handling retry/resume
		if ( has_filter( 'wpshadow_operation_recovery_script' ) ) {
			return true;
		}

		return false;
	}
}
