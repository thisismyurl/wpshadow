<?php
/**
 * Search Engine Indexing Status Not Checked Diagnostic
 *
 * Checks if site is indexed.
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
 * Search Engine Indexing Status Not Checked Diagnostic Class
 *
 * Detects unchecked indexing status.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Search_Engine_Indexing_Status_Not_Checked extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'search-engine-indexing-status-not-checked';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Search Engine Indexing Status Not Checked';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if site is indexed';

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
		// Check if blog is public
		if ( get_option( 'blog_public' ) === '0' ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Search engine indexing status is not checked. Monitor your indexing status in Google Search Console to ensure pages are being discovered.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/search-engine-indexing-status-not-checked?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
