<?php
/**
 * Mobile Skip Links Treatment
 *
 * Ensures skip-to-content link is visible on mobile.
 *
 * @since   1.6033.1645
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Skip Links Treatment Class
 *
 * Ensures skip-to-content link exists and is keyboard accessible,
 * allowing users to bypass navigation blocks (WCAG 2.4.1).
 *
 * @since 1.6033.1645
 */
class Treatment_Mobile_Skip_Links extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-skip-links';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Skip Links';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure skip-to-content link is present and keyboard accessible (WCAG 2.4.1)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Skip_Links' );
	}
}
