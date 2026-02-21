<?php
/**
 * Mobile Language Declaration Treatment
 *
 * Validates HTML lang attribute is properly set.
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
 * Mobile Language Declaration Treatment Class
 *
 * Validates that the HTML lang attribute is set and properly formatted,
 * ensuring screen readers pronounce text correctly (WCAG 3.1.1).
 *
 * @since 1.6033.1645
 */
class Treatment_Mobile_Language_Declaration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-language-declaration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Language Declaration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validate HTML lang attribute set with correct format (WCAG 3.1.1)';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Language_Declaration' );
	}
}
