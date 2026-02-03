<?php
/**
 * JavaScript Deserialization Not Protected Diagnostic
 *
 * Checks JS deserialization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_JavaScript_Deserialization_Not_Protected Class
 *
 * Performs diagnostic check for Javascript Deserialization Not Protected.
 *
 * @since 1.26033.2033
 */
class Diagnostic_JavaScript_Deserialization_Not_Protected extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'javascript-deserialization-not-protected';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'JavaScript Deserialization Not Protected';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks JS deserialization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'protect_js_deserialization' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('JavaScript deserialization not protected. Never eval() user input and use JSON.parse() with try-catch.',
						'severity'   =>   'high',
						'threat_level'   =>   70,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/javascript-deserialization-not-protected'
						);
						);,
						);
						}
						return null;
						}
						return null;
						}
						return null;
	}
}
