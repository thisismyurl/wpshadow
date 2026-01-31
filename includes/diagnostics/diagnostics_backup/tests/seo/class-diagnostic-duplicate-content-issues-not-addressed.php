<?php
/**
 * Duplicate Content Issues Not Addressed Diagnostic
 *
 * Checks if duplicate content is addressed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2345
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Duplicate Content Issues Not Addressed Diagnostic Class
 *
 * Detects duplicate content handling.
 *
 * @since 1.2601.2345
 */
class Diagnostic_Duplicate_Content_Issues_Not_Addressed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'duplicate-content-issues-not-addressed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate Content Issues Not Addressed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if duplicate content is addressed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2345
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for canonical URL filter
		if ( ! has_filter( 'rel_canonical' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Canonical URLs are not configured. Add canonical tags to prevent duplicate content penalties.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/duplicate-content-issues-not-addressed',
			);
		}

		return null;
	}
}
