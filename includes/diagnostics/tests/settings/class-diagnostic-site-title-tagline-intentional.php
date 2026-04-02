<?php
/**
 * Site Title And Tagline Intentional Diagnostic
 *
 * Checks whether the site title and tagline have been updated from the WordPress
 * default placeholder values that appear in browser tabs and search results.
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
 * Diagnostic_Site_Title_Tagline_Intentional Class
 *
 * Uses WP_Settings helpers to detect whether blogname or blogdescription
 * contain default placeholder text, returning a low-severity finding.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Site_Title_Tagline_Intentional extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'site-title-tagline-intentional';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Site Title And Tagline Intentional';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the site title and tagline have been updated from the WordPress default placeholder values that appear in browser tabs and search results.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Calls WP_Settings::is_default_site_title() and is_default_tagline() to
	 * determine whether either value is still a WordPress installer default.
	 * Returns null when both have been customised. When one or both are defaults,
	 * collects the specific issue strings and returns a low-severity finding.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when default values are detected, null when healthy.
	 */
	public static function check() {
		$title_default   = WP_Settings::is_default_site_title();
		$tagline_default = WP_Settings::is_default_tagline();

		if ( ! $title_default && ! $tagline_default ) {
			return null;
		}

		$issues = array();
		if ( $title_default ) {
			$issues[] = sprintf(
				/* translators: %s: current site title */
				__( 'Site title is "%s" (a default or empty value).', 'wpshadow' ),
				WP_Settings::get_site_title()
			);
		}
		if ( $tagline_default ) {
			$issues[] = sprintf(
				/* translators: %s: current tagline */
				__( 'Tagline is "%s" (a default or empty value).', 'wpshadow' ),
				WP_Settings::get_tagline()
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your site title or tagline still appears to be a WordPress default or empty. These values appear in browser tabs, search results, and social media previews — set them intentionally to reflect your brand.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 10,
			'kb_link'      => 'https://wpshadow.com/kb/site-title-tagline?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'issues'      => $issues,
				'site_title'  => WP_Settings::get_site_title(),
				'tagline'     => WP_Settings::get_tagline(),
			),
		);
	}
}
