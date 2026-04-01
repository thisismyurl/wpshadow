<?php
/**
 * Mobile Code Block Rendering Treatment
 *
 * Tests if code blocks render legibly on mobile.
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
 * Mobile Code Block Rendering Treatment Class
 *
 * Checks for code block markup and styling hints on the homepage.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Code_Block_Rendering extends Treatment_Base {

	protected static $slug = 'mobile-code-block-rendering';
	protected static $title = 'Mobile Code Block Rendering';
	protected static $description = 'Tests if code blocks render legibly on mobile';
	protected static $family = 'design';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Code_Block_Rendering' );
	}
}
