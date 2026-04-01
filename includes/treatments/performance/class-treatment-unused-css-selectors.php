<?php
/**
 * Unused CSS Selectors Treatment
 *
 * Detects unused CSS selectors and optimization opportunities.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Unused CSS Selectors Treatment
 *
 * Identifies unused CSS that can be removed to reduce file size.
 *
 * @since 0.6093.1200
 */
class Treatment_Unused_CSS_Selectors extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'unused-css-selectors';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Unused CSS Selectors';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects unused CSS selectors and optimization opportunities';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Unused_CSS_Selectors' );
	}
}
