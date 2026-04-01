<?php
/**
 * Mobile Breadcrumb Navigation
 *
 * Validates breadcrumb implementation for mobile usability.
 *
 * @package    WPShadow
 * @subpackage Treatments\Navigation
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Breadcrumb Navigation
 *
 * Ensures breadcrumb navigation exists and is mobile-friendly
 * with appropriate sizing and schema markup.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Breadcrumb extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-breadcrumb-navigation';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Breadcrumb Navigation';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates breadcrumbs for mobile usability';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'navigation';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Breadcrumb' );
	}
}
