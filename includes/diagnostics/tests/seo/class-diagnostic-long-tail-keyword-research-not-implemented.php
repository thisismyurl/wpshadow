<?php
/**
 * Long Tail Keyword Research Not Implemented Diagnostic
 *
 * Checks if long tail keywords are researched.
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
 * Long Tail Keyword Research Not Implemented Diagnostic Class
 *
 * Detects missing long tail keyword research.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Long_Tail_Keyword_Research_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'long-tail-keyword-research-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Long Tail Keyword Research Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if long tail keywords are researched';

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
		// Check for keyword research tools integration
		if ( ! is_plugin_active( 'rank-math/rank-math.php' ) && ! is_plugin_active( 'yoast-seo/wp-seo.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Long tail keyword research is not implemented. Research and target long tail keywords to attract highly qualified traffic.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/long-tail-keyword-research-not-implemented',
			);
		}

		return null;
	}
}
