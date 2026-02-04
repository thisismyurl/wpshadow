<?php
/**
 * Internationalization Not Properly Configured Diagnostic
 *
 * Checks i18n.
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
 * Diagnostic_Internationalization_Not_Properly_Configured Class
 *
 * Performs diagnostic check for Internationalization Not Properly Configured.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Internationalization_Not_Properly_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'internationalization-not-properly-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Internationalization Not Properly Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks i18n';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'validate_i18n_setup' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Internationalization not properly configured. Use load_plugin_textdomain() and translate all user-facing strings.',
						'severity'   =>   'low',
						'threat_level'   =>   10,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/internationalization-not-properly-configured'
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
