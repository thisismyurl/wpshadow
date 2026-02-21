<?php
/**
 * Permalink Structure Not Optimized Treatment
 *
 * Tests for SEO-friendly permalink configuration.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permalink Structure Not Optimized Treatment Class
 *
 * Tests for SEO-friendly permalink structure.
 *
 * @since 1.6033.0000
 */
class Treatment_Permalink_Structure_Not_Optimized extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'permalink-structure-not-optimized';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Permalink Structure Not Optimized';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for SEO-friendly permalink configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Permalink_Structure_Not_Optimized' );
	}
}
