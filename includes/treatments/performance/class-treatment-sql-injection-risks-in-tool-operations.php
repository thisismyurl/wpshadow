<?php
/**
 * SQL Injection Risks in Tool Operations Treatment
 *
 * Tests for SQL injection vulnerability prevention.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SQL Injection Risks in Tool Operations Treatment Class
 *
 * Tests for SQL injection vulnerability prevention in tool operations.
 *
 * @since 1.6093.1200
 */
class Treatment_SQL_Injection_Risks_In_Tool_Operations extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'sql-injection-risks-in-tool-operations';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SQL Injection Risks in Tool Operations';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for SQL injection vulnerability prevention';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_SQL_Injection_Risks_In_Tool_Operations' );
	}
}
