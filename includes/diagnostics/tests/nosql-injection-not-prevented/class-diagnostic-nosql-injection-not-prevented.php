<?php
/**
 * NoSQL Injection Not Prevented Diagnostic
 *
 * Checks NoSQL injection.
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
 * Diagnostic_NoSQL_Injection_Not_Prevented Class
 *
 * Performs diagnostic check for Nosql Injection Not Prevented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_NoSQL_Injection_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'nosql-injection-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'NoSQL Injection Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks NoSQL injection';

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
						'sanitize_nosql_queries' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('NoSQL injection not prevented. Parameterize all NoSQL queries and validate/sanitize query operators like $where and $regex.',
						'severity'   =>   'high',
						'threat_level'   =>   70,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/nosql-injection-not-prevented'
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
