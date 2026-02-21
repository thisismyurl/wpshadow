<?php
/**
 * Mobile Link Underlines Treatment
 *
 * Ensures links are visually distinguishable on mobile.
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
 * Mobile Link Underlines Treatment Class
 *
 * Ensures links are visually distinguishable from body text through underlines
 * or color differentiation, following WCAG 1.4.1.
 *
 * @since 1.6033.1645
 */
class Treatment_Mobile_Link_Underlines extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-link-underlines';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Link Underlines';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure links are visually distinguishable from body text (WCAG 1.4.1)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Link_Underlines' );
	}
}
