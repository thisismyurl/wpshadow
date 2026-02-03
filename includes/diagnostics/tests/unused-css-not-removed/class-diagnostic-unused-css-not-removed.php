<?php
/**
 * Unused CSS Not Removed Diagnostic
 *
 * Checks unused CSS removal.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Unused_CSS_Not_Removed Class
 *
 * Performs diagnostic check for Unused Css Not Removed.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Unused_CSS_Not_Removed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'unused-css-not-removed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Unused CSS Not Removed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks unused CSS removal';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('wp_head',
						'remove_unused_css' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Unused CSS not removed. Use PurgeCSS or similar tools to remove unused styles and reduce CSS file sizes by 50-80%.',
						'severity'   =>   'medium',
						'threat_level'   =>   40,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/unused-css-not-removed'
						);
						);,
						);
						}
						return null;
						}
						return null;
						}
						return null;
	}
}
