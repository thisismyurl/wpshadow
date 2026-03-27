<?php
/**
 * Mobile Screen Reader Compatibility Treatment
 *
 * Validates ARIA labels and landmarks work correctly on mobile screen readers.
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
 * Mobile Screen Reader Compatibility Treatment Class
 *
 * Validates that ARIA labels and landmarks work correctly on mobile screen readers
 * (VoiceOver, TalkBack) for WCAG A/AA compliance and accessibility.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Screen_Reader_Compatibility extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-screen-reader-compatibility';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Screen Reader Compatibility';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validate ARIA labels and landmarks work correctly on mobile screen readers';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Screen_Reader_Compatibility' );
	}
}
