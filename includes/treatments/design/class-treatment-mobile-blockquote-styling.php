<?php
/**
 * Mobile Blockquote Styling Treatment
 *
 * Tests if blockquotes are styled clearly on mobile.
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
 * Mobile Blockquote Styling Treatment Class
 *
 * Checks for blockquote styling classes on the homepage.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Blockquote_Styling extends Treatment_Base {

	protected static $slug = 'mobile-blockquote-styling';
	protected static $title = 'Mobile Blockquote Styling';
	protected static $description = 'Tests if blockquotes are styled clearly on mobile';
	protected static $family = 'design';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Blockquote_Styling' );
	}
}
