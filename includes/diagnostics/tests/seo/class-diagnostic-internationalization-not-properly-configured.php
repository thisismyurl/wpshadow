<?php
/**
 * Internationalization Not Properly Configured Diagnostic
 *
 * Checks i18n.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
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
 * @since 0.6093.1200
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
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'validate_i18n_setup' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Internationalization is not fully configured yet. Loading translations with load_plugin_textdomain() helps visitors read your site in their language.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 10,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/internationalization-not-properly-configured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
