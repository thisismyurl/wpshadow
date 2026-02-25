<?php
/**
 * Tool Nonce Validation Failures
 *
 * Comprehensive test of nonce implementation across all Tool actions.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tools
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Tool_Nonce_Validation_Failures Class
 *
 * Comprehensive validation of nonce implementation across tool actions.
 *
 * @since 1.6030.2148
 */
class Treatment_Tool_Nonce_Validation_Failures extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'tool-nonce-validation-failures';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Tool Nonce Validation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Comprehensive test of nonce implementation in tool actions';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the treatment check.
	 *
	 * Tests nonce validation comprehensively.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Tool_Nonce_Validation_Failures' );
	}
}
