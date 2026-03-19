<?php
/**
 * Form Auto-Save Draft Treatment
 *
 * Detects when long forms don't automatically save user progress as a draft.
 *
 * @package    WPShadow
 * @subpackage Treatments\UX
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments\UX;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Auto-Save Draft Treatment Class
 *
 * Checks if long forms have auto-save functionality to prevent data loss.
 *
 * @since 1.6093.1200
 */
class Treatment_Form_Auto_Save_Draft extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-auto-save-draft';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Long Forms Don\'t Auto-Save Draft Progress';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when long forms lack auto-save functionality to protect user input';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'ux';

	/**
	 * Run the treatment check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\UX\Diagnostic_Form_Auto_Save_Draft' );
	}
}
