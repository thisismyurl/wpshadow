<?php
/**
 * Source Map Not Deployed Production Diagnostic
 *
 * Checks source map deployment.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Source_Map_Not_Deployed_Production Class
 *
 * Performs diagnostic check for Source Map Not Deployed Production.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Source_Map_Not_Deployed_Production extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'source-map-not-deployed-production';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Source Map Not Deployed Production';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks source map deployment';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   file_exists(ABSPATH.'js/app.js.map' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Source maps deployed to production. Remove .map files from production to prevent source code exposure and reduce file sizes.',
						'severity'   =>   'medium',
						'threat_level'   =>   35,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/source-map-not-deployed-production'
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
