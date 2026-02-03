<?php
/**
 * Version Control Not Isolated Diagnostic
 *
 * Checks version control isolation.
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
 * Diagnostic_Version_Control_Not_Isolated Class
 *
 * Performs diagnostic check for Version Control Not Isolated.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Version_Control_Not_Isolated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'version-control-not-isolated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Version Control Not Isolated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks version control isolation';

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
		if (   !file_exists(ABSPATH.'.gitignore' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Version control not isolated. Exclude sensitive files (.env,
						'severity'   =>   'high',
						'threat_level'   =>   80,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/version-control-not-isolated'
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
