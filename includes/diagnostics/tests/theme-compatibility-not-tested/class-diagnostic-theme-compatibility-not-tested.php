<?php
/**
 * Theme Compatibility Not Tested Diagnostic
 *
 * Checks theme compatibility testing.
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
 * Diagnostic_Theme_Compatibility_Not_Tested Class
 *
 * Performs diagnostic check for Theme Compatibility Not Tested.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Theme_Compatibility_Not_Tested extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-compatibility-not-tested';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Compatibility Not Tested';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks theme compatibility testing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !get_option('theme_compatibility_test_date' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Theme compatibility not tested. Test plugins with all active themes to ensure compatibility and prevent conflicts or display issues.',
						'severity'   =>   'low',
						'threat_level'   =>   15,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/theme-compatibility-not-tested'
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
