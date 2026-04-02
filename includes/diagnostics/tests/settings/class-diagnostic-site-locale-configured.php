<?php
/**
 * Site Locale Configured Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 45.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Locale Configured Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Site_Locale_Configured extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'site-locale-configured';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Site Locale Configured';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Stub diagnostic for Site Locale Configured. TODO: implement full test and remediation guidance.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Use get_locale and WPLANG consistency checks.
	 *
	 * TODO Fix Plan:
	 * Fix by selecting target locale.
	 *
	 * Constraints:
	 * - Must be testable using built-in WordPress functions or PHP checks.
	 * - Must be fixable via hooks/filters/settings/DB/PHP/server setting.
	 * - Must not modify WordPress core files.
	 * - Must improve performance, security, or site success.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
	 */
	public static function check() {
		$locale = WP_Settings::get_locale();

		// An empty WPLANG option means the admin has never explicitly chosen a locale;
		// WordPress silently defaults to en_US. Flag this for review.
		if ( '' !== $locale ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your site language has never been explicitly configured. WordPress defaulted to English (US). If your audience speaks a different language, visit Settings > General and choose the correct site language.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 10,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/site-locale-configured',
			'details'      => array(
				'current_locale'  => get_locale(),
				'wplang_option'   => $locale,
			),
		);
	}
}
