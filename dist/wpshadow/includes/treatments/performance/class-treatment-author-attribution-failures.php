<?php
/**
 * Author Attribution Failures Treatment
 *
 * Detects when imported posts lose correct author assignments.
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
 * Author Attribution Failures Treatment Class
 *
 * Detects when imported posts lose correct author assignments or have wrong authors.
 *
 * @since 1.6093.1200
 */
class Treatment_Author_Attribution_Failures extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'author-attribution-failures';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Author Attribution Failures';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when imported posts have incorrect author assignments';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Author_Attribution_Failures' );
	}
}
