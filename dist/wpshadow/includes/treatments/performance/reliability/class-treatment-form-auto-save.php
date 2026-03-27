<?php
/**
 * Form Auto-Save Treatment
 *
 * Issue #4855: Long Forms Don't Auto-Save Draft Progress
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if long forms auto-save draft progress to prevent data loss.
 * Lost form data due to browser crash or accidental navigation causes frustration and abandonment.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Form_Auto_Save Class
 *
 * Checks for:
 * - JavaScript that saves form data periodically
 * - Local storage or IndexedDB usage for draft storage
 * - Recovery/restore mechanism on page reload
 * - Clear indication to user that draft exists
 *
 * Auto-save protects users from data loss due to:
 * - Accidental browser/tab closure
 * - Network interruption mid-submission
 * - Server timeout during processing
 * - Browser crash or computer restart
 *
 * @since 1.6093.1200
 */
class Treatment_Form_Auto_Save extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'form-auto-save';

	/**
	 * The treatment title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Long Forms Don\'t Auto-Save Draft Progress';

	/**
	 * The treatment description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if long forms auto-save draft progress to prevent data loss';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Form_Auto_Save' );
	}
}
