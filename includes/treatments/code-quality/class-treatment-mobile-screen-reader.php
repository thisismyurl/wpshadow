<?php
/**
 * Mobile Screen Reader Compatibility
 *
 * Validates ARIA labels and landmarks for mobile screen readers.
 *
 * @package    WPShadow
 * @subpackage Treatments\Accessibility
 * @since      1.602.1430
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Screen Reader Compatibility
 *
 * Validates that ARIA labels, landmarks, and semantic HTML work correctly
 * with mobile screen readers (VoiceOver, TalkBack).
 * WCAG 4.1.2 Level A requirement.
 *
 * @since 1.602.1430
 */
class Treatment_Mobile_Screen_Reader extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-screen-reader-issues';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Screen Reader Compatibility';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates ARIA labels and landmarks for screen readers';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Screen_Reader' );
	}
}
