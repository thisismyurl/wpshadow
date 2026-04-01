<?php
/**
 * Error Recovery Capability Treatment
 *
 * Issue #4873: Users Can't Recover From Errors (No Undo)
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if destructive operations have confirmation and undo capability.
 * "Oops, I didn't mean to do that" should be recoverable.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Error_Recovery_Capability Class
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
class Treatment_Error_Recovery_Capability extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $slug = 'error-recovery-capability';

	/**
	 * The treatment title
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $title = 'Users Can\'t Recover From Errors (No Undo)';

	/**
	 * The treatment description
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if destructive operations have confirmation and undo capability';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Error_Recovery_Capability' );
	}
}
