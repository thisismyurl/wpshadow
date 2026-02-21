<?php
/**
 * Cryptic Tool Error Messages Treatment
 *
 * Provides treatment mapping for cryptic tool error message diagnostics.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cryptic Tool Error Messages Treatment Class
 *
 * @since 1.6030.2148
 */
class Treatment_Cryptic_Tool_Error_Messages extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'cryptic-tool-error-messages';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Cryptic Tool Error Messages';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects unclear error messages without fix guidance';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Cryptic_Tool_Error_Messages' );
	}
}
