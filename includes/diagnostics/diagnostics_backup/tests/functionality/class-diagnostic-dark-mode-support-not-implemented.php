<?php
/**
 * Dark Mode Support Not Implemented Diagnostic
 *
 * Checks if dark mode is supported.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2340
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dark Mode Support Not Implemented Diagnostic Class
 *
 * Detects missing dark mode support.
 *
 * @since 1.2601.2340
 */
class Diagnostic_Dark_Mode_Support_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dark-mode-support-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Dark Mode Support Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if dark mode is supported';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if theme supports dark mode via CSS custom properties
		if ( ! current_theme_supports( 'dark-mode' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Dark mode is not supported. Add dark mode support using CSS custom properties or a dark mode plugin.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/dark-mode-support-not-implemented',
			);
		}

		return null;
	}
}
