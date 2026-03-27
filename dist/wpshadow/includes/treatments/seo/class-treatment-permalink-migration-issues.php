<?php
/**
 * Permalink Migration Issues Treatment
 *
 * Detects potential issues when migrating from one permalink structure to another,
 * ensuring proper redirects are in place to prevent broken links.
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
 * Permalink Migration Issues Treatment Class
 *
 * Identifies permalink structure changes and checks for proper redirect handling.
 *
 * @since 1.6093.1200
 */
class Treatment_Permalink_Migration_Issues extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'permalink-migration-issues';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Permalink Migration Issues';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects permalink structure migration issues';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'permalinks';

	/**
	 * Run the treatment check.
	 *
	 * Checks:
	 * - Permalink structure changes in history
	 * - Redirect plugins installed
	 * - Old URLs still being indexed
	 * - 404 error patterns
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Permalink_Migration_Issues' );
	}
}
