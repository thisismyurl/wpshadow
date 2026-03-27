<?php
/**
 * Permalink 404 Errors Treatment
 *
 * Detects 404 errors from broken permalinks and tests URL accessibility.
 * This treatment identifies common permalink configuration issues that can
 * cause broken links and poor user experience.
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
 * Permalink 404 Errors Treatment Class
 *
 * Checks for broken permalinks and URL accessibility issues.
 *
 * @since 1.6093.1200
 */
class Treatment_Permalink_404_Errors extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'permalink-404-errors';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Permalink 404 Errors';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects 404 errors from broken permalinks and tests URL accessibility';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Permalink_404_Errors' );
	}
}
