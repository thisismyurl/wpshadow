<?php
/**
 * External Links Not Opening In New Tab Diagnostic
 *
 * Checks if external links open in new tab.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * External Links Not Opening In New Tab Diagnostic Class
 *
 * Detects external links not opening in new tab.
 *
 * @since 1.6030.2352
 */
class Diagnostic_External_Links_Not_Opening_In_New_Tab extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'external-links-not-opening-in-new-tab';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'External Links Not Opening In New Tab';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if external links open in new tab';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if external link filter is active
		if ( ! has_filter( 'the_content', 'open_external_links_new_tab' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'External links are not set to open in new tab. Add target="_blank" with rel="noopener noreferrer" to external links to improve UX.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/external-links-not-opening-in-new-tab',
			);
		}

		return null;
	}
}
