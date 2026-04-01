<?php
/**
 * Permalink Structure SEO Treatment
 *
 * Verifies permalink structure is SEO-friendly and follows best practices.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permalink Structure SEO Treatment Class
 *
 * Analyzes permalink configuration for SEO optimization.
 *
 * @since 0.6093.1200
 */
class Treatment_Permalink_Structure_SEO extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'permalink-structure-seo';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Permalink Structure SEO';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies permalink structure is SEO-friendly';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'permalinks';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Permalink_Structure_SEO' );
	}
}
