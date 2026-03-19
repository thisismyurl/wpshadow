<?php
/**
 * Function Argument Type Checking Not Enforced Diagnostic
 *
 * Checks type hints.
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
 * Diagnostic_Function_Argument_Type_Checking_Not_Enforced Class
 *
 * Performs diagnostic check for Function Argument Type Checking Not Enforced.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Function_Argument_Type_Checking_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'function-argument-type-checking-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Function Argument Type Checking Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks type hints';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'enforce_type_hints' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Function argument type checking is not enforced yet. Adding type hints can improve reliability and reduce runtime mistakes.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 10,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/function-argument-type-checking-not-enforced',
			);
		}

		return null;
	}
}
