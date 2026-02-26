<?php
/**
 * Unsafe Reflection Not Prevented Diagnostic
 *
 * Checks unsafe reflection.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Unsafe_Reflection_Not_Prevented Class
 *
 * Performs diagnostic check for Unsafe Reflection Not Prevented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Unsafe_Reflection_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'unsafe-reflection-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Unsafe Reflection Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks unsafe reflection';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'prevent_unsafe_reflection' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unsafe reflection protections are not detected yet. Restricting dynamic class and method resolution helps prevent unsafe execution paths.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/unsafe-reflection-not-prevented',
			);
		}

		return null;
	}
}
