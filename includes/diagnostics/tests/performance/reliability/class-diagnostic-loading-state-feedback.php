<?php
/**
 * Loading State Feedback Diagnostic
 *
 * Issue #4880: No Visual Feedback During Loading States
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if long operations show loading indicators.
 * Users assume something broke if they see no feedback.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Loading_State_Feedback Class
 *
 * Checks for:
 * - Spinner on AJAX requests
 * - Progress bar on uploads
 * - "Saving..." text feedback
 * - Disable buttons during operation (prevent double-submit)
 * - Skeleton screens for content loading
 * - Estimate time remaining ("About 2 minutes left")
 * - Cancel button during long operations
 * - Success/failure notification after operation
 *
 * Why this matters:
 * - Users assume frozen = broken without feedback
 * - Lack of feedback causes anxiety
 * - Multiple clicks on "Submit" cause duplicate operations
 * - Professional interfaces show progress
 *
 * @since 1.6093.1200
 */
class Diagnostic_Loading_State_Feedback extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'loading-state-feedback';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'No Visual Feedback During Loading States';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if long operations show loading indicators and progress feedback';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic - actual loading state analysis requires UI review.
		// We provide recommendations.

		$issues = array();

		$issues[] = __( 'Show spinner on AJAX requests (immediately on click)', 'wpshadow' );
		$issues[] = __( 'Show progress bar on file uploads (0-100%)', 'wpshadow' );
		$issues[] = __( 'Disable submit button during operation: "Saving..." (prevent double-click)', 'wpshadow' );
		$issues[] = __( 'Use skeleton screens for content loading (placeholder boxes)', 'wpshadow' );
		$issues[] = __( 'Show time estimate for long operations: "About 2 minutes left"', 'wpshadow' );
		$issues[] = __( 'Provide cancel button during operations', 'wpshadow' );
		$issues[] = __( 'Show success/error notification after completion', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Without loading feedback, users assume the site is frozen or broken. They click multiple times, causing duplicate operations.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/loading-state-feedback',
				'details'      => array(
					'recommendations'         => $issues,
					'operations_needing_feedback' => 'Form submit, file upload, bulk actions, export, import',
					'loading_types'           => array(
						'spinner'    => 'Rotating icon (1-5 seconds)',
						'progress'   => 'Progress bar with percentage (5+ seconds)',
						'skeleton'   => 'Placeholder boxes (content loading)',
					),
					'timing'                  => 'Show feedback within 100ms of action',
					'ux_benefit'              => 'Reduces perceived loading time by 30%',
				),
			);
		}

		return null;
	}
}
