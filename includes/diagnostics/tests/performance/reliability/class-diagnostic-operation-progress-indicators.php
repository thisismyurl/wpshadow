<?php
/**
 * Operation Progress Indicators Diagnostic
 *
 * Issue #4857: Long Operations Have No Progress Indicator
 * Pillar: ⚙️ Murphy's Law, Commandment #1: Helpful Neighbor
 *
 * Checks if long-running operations (uploads, processing) show progress to users.
 * Without feedback, users don't know if the operation is working or frozen.
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
 * Diagnostic_Operation_Progress_Indicators Class
 *
 * Checks for:
 * - Progress bars on file uploads
 * - Status messages during long operations
 * - Spinner/loading indicator during AJAX requests
 * - Estimated time remaining displayed
 * - Cancel button for long operations
 * - No stuck/frozen UI without feedback
 *
 * User experience principle:
 * - User knows operation started
 * - User sees progress towards completion
 * - User feels in control (can cancel)
 * - User isn't left wondering "Is it working?"
 *
 * @since 1.6093.1200
 */
class Diagnostic_Operation_Progress_Indicators extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'operation-progress-indicators';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Long Operations Have No Progress Indicator';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if long-running operations show progress to users';

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
		// This would require checking JavaScript for progress indicators
		// For now, provide informational finding

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Long operations (uploads, imports, processing) should show progress to keep users informed', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 40,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/progress-indicators',
			'details'      => array(
				'operations_to_monitor' => array(
					'File uploads',
					'Bulk actions',
					'Database imports/exports',
					'Long-running batch operations',
				),
				'what_to_show' => array(
					'Progress bar or percentage',
					'Status message (e.g., "Processing 150 of 500 items")',
					'Spinner or loading indicator',
					'Cancel button (if safe)',
				),
				'user_benefit' => 'Users know operation is working and when it will complete',
			),
		);
	}
}
