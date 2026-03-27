<?php
/**
 * Permalink Rewrite Rules Treatment
 *
 * Verifies permalink rewrite rules are properly configured and not corrupted.
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
 * Permalink Rewrite Rules Treatment Class
 *
 * Checks the integrity and functionality of WordPress rewrite rules.
 *
 * @since 1.6093.1200
 */
class Treatment_Permalink_Rewrite_Rules extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'permalink-rewrite-rules';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Permalink Rewrite Rules';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies rewrite rules are properly configured';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'permalinks';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Permalink_Rewrite_Rules' );
	}
}
