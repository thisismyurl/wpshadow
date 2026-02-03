<?php
/**
 * Static Analysis Not Automated Diagnostic
 *
 * Checks static analysis.
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
 * Diagnostic_Static_Analysis_Not_Automated Class
 *
 * Performs diagnostic check for Static Analysis Not Automated.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Static_Analysis_Not_Automated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'static-analysis-not-automated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Static Analysis Not Automated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks static analysis';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'run_static_analysis' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Static analysis not automated. Use PHPStan,
						'severity'   =>   'medium',
						'threat_level'   =>   35,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/static-analysis-not-automated'
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
