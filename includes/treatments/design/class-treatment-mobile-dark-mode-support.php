<?php
/**
 * Mobile Dark Mode Support Treatment
 *
 * Validates support for OS-level dark mode preference.
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
 * Mobile Dark Mode Support Treatment Class
 *
 * Validates that the site respects prefers-color-scheme media query,
 * providing a dark theme option for users who prefer it.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Dark_Mode_Support extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-dark-mode-support';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Dark Mode Support';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Support OS-level dark mode preference with proper contrast';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Dark_Mode_Support' );
	}
}
