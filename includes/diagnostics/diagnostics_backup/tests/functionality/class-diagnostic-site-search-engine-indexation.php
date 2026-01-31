<?php
/**
 * Site Search Engine Indexation Diagnostic
 *
 * Checks if site is being indexed by search engines.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Search Engine Indexation Diagnostic Class
 *
 * Detects indexation issues.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Site_Search_Engine_Indexation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-search-engine-indexation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Search Engine Indexation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if site is indexed by search engines';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if search engine visibility is enabled
		$blog_public = get_option( 'blog_public', 1 );

		if ( ! $blog_public ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Site visibility to search engines is disabled. Enable it in Settings > Reading to allow indexation.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/site-search-engine-indexation',
			);
		}

		return null;
	}
}
