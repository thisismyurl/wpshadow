<?php
/**
 * Mobile Font Size - Body Text Treatment
 *
 * Validates minimum font size for body text on mobile to ensure readability.
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
 * Mobile Font Size - Body Text Treatment Class
 *
 * Validates minimum font size for body text on mobile to ensure readability
 * without forcing pinch-zoom, prevents iOS auto-zoom issues.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Font_Size_Body_Text extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-font-size-body-text';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Font Size - Body Text';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validate minimum font size for body text on mobile (16px minimum to prevent iOS auto-zoom)';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Font_Size_Body_Text' );
	}
}
