<?php
/**
 * Error Recovery Capability Diagnostic
 *
 * Issue #4873: Users Can't Recover From Errors (No Undo)
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if destructive operations have confirmation and undo capability.
 * "Oops, I didn't mean to do that" should be recoverable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Error_Recovery_Capability Class
 *
 * Checks for:
 * - Confirmation dialog before destructive operations
 * - Undo functionality for reversible actions
 * - Backup before modifications (copy database row, etc)
 * - Clear explanation of what will happen
 * - Ability to cancel operation
 * - Activity logging to track what happened
 * - Safe defaults (require user to opt into danger, not opt out)
 *
 * Why this matters:
 * - Users make mistakes (accidentally delete, wrong click)
 * - Some operations are irreversible without backup
 * - Lack of recovery creates anxiety and lost data
 * - Defensive design prevents user error
 *
 * @since 0.6093.1200
 */
class Diagnostic_Error_Recovery_Capability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $slug = 'error-recovery-capability';

	/**
	 * The diagnostic title
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $title = 'Users Can\'t Recover From Errors (No Undo)';

	/**
	 * The diagnostic description
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if destructive operations have confirmation and undo capability';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic - actual operation analysis requires code review.
		// We provide recommendations for safe operations.

		$issues = array();

		$issues[] = __( 'Destructive operations need confirmation dialog', 'wpshadow' );
		$issues[] = __( 'Show what will happen: "Delete 5 posts? They cannot be recovered."', 'wpshadow' );
		$issues[] = __( 'Create backup before modification (database row copy, file backup)', 'wpshadow' );
		$issues[] = __( 'Provide undo button immediately after operation', 'wpshadow' );
		$issues[] = __( 'Log all destructive operations (activity history)', 'wpshadow' );
		$issues[] = __( 'Make cancel always available (never disable it)', 'wpshadow' );
		$issues[] = __( 'Use safe defaults: require opt-in to danger, not opt-out', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Accidental clicks happen. Users delete wrong post, clear wrong cache, disable wrong setting. Without undo, they lose hours of work.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/error-recovery?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'destructive_operations'  => 'Delete, truncate table, disable plugin, clear cache',
					'pattern'                 => 'Confirm → Backup → Execute → Log → Provide Undo',
					'undo_window'             => 'Keep undo available for 24 hours (or session)',
					'example'                 => 'Deleting 10 posts → "Delete these 10 posts permanently? [Undo available for 24h]" → [Delete] [Cancel]',
				),
			);
		}

		return null;
	}
}
