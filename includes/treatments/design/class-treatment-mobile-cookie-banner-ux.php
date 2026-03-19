<?php
/**
 * Mobile Cookie Banner UX Treatment
 *
 * Optimizes cookie consent banner for mobile devices.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Cookie Banner UX Treatment Class
 *
 * Validates cookie consent banner is properly formatted for mobile,
 * ensuring GDPR compliance and good UX without blocking main content.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Cookie_Banner_UX extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-cookie-banner-ux';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Cookie Banner UX';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Optimize cookie consent banner for mobile without blocking main content';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Cookie_Banner_UX' );
	}
}
