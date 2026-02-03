<?php
/**
 * Path Traversal Attack Not Prevented Diagnostic
 *
 * Checks path traversal.
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
 * Diagnostic_Path_Traversal_Attack_Not_Prevented Class
 *
 * Performs diagnostic check for Path Traversal Attack Not Prevented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Path_Traversal_Attack_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'path-traversal-attack-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Path Traversal Attack Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks path traversal';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'validate_file_paths' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Path traversal attack not prevented. Use realpath() and restrict file operations to allowed directories.',
						'severity'   =>   'high',
						'threat_level'   =>   75,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/path-traversal-attack-not-prevented'
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
