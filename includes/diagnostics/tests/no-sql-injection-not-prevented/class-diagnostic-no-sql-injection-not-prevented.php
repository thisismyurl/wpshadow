<?php
/**
 * NoSQL Injection Not Prevented Diagnostic
 *
 * Checks NoSQL injection prevention.
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
 * Diagnostic_No_SQL_Injection_Not_Prevented Class
 *
 * Performs diagnostic check for No Sql Injection Not Prevented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_No_SQL_Injection_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-sql-injection-not-prevented';

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
	protected static $description = 'Checks NoSQL injection prevention';

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
						'validate_nosql_queries' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('NoSQL injection not prevented. Use schema validation and parameterized queries for all NoSQL operations.',
						'severity'   =>   'high',
						'threat_level'   =>   75,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/no-sql-injection-not-prevented'
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
