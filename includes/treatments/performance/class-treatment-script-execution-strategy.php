<?php
/**
 * Script Execution Strategy Treatment
 *
 * Analyzes JavaScript execution strategy (inline, defer, async) for optimal
 * page load performance and rendering optimization.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Script Execution Strategy Treatment Class
 *
 * Verifies script optimization:
 * - Async vs defer ratio
 * - Inline critical scripts
 * - Heavy script detection
 * - Script loading order
 *
 * @since 1.6093.1200
 */
class Treatment_Script_Execution_Strategy extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'script-execution-strategy';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Script Execution Strategy';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes JavaScript execution strategy for optimal performance';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Script_Execution_Strategy' );
	}
}
