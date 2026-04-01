<?php
/**
 * Code Splitting Strategy Treatment
 *
 * Tests if JavaScript and CSS are properly split to reduce
 * initial bundle sizes and improve page load performance.
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
 * Code Splitting Strategy Treatment Class
 *
 * Evaluates whether the site uses code splitting techniques
 * to optimize JavaScript and CSS delivery.
 *
 * @since 0.6093.1200
 */
class Treatment_Code_Splitting_Strategy extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'uses-code-splitting';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Code Splitting Strategy';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if JavaScript and CSS are properly split';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the code splitting strategy treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if code splitting issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Code_Splitting_Strategy' );
	}
}
