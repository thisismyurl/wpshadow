<?php
/**
 * Site Icon Configured Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 58.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Icon Configured Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Site_Icon_extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'site-icon';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Site Icon';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether a site icon (favicon) has been configured in WordPress, which appears in browser tabs, bookmarks, and search engine results.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Check get_site_icon_url returns valid icon.
	 *
	 * TODO Fix Plan:
	 * Fix by setting site icon in customizer.
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
		$site_icon_id = (int) get_option( 'site_icon', 0 );
		if ( $site_icon_id > 0 ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No site icon (favicon) has been set. The site icon appears in browser tabs, bookmarks, mobile home screens, and Google Search results. Set one under Appearance > Customize > Site Identity.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 15,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/site-icon',
			'details'      => array(
				'site_icon_id' => 0,
			),
		);
	}
}
