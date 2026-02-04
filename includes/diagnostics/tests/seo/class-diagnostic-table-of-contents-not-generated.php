<?php
/**
 * Table Of Contents Not Generated Diagnostic
 *
 * Checks if table of contents is generated.
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
 * Table Of Contents Not Generated Diagnostic Class
 *
 * Detects missing table of contents generation.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Table_Of_Contents_Not_Generated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'table-of-contents-not-generated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Table Of Contents Not Generated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if table of contents is generated';

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
		// Check for TOC plugin
		if ( ! is_plugin_active( 'easy-table-of-contents/easy-table-of-contents.php' ) && ! has_filter( 'the_content', 'generate_table_of_contents' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Table of contents is not generated. Add a TOC for long-form content to improve user experience and SEO.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/table-of-contents-not-generated',
			);
		}

		return null;
	}
}
