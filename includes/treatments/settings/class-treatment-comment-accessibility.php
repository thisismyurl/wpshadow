<?php
/**
 * Comment Accessibility and Display Quality
 *
 * Validates comment section accessibility and rendering quality.
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
 * Treatment_Comment_Accessibility Class
 *
 * Checks comment section accessibility and display quality.
 *
 * @since 1.6093.1200
 */
class Treatment_Comment_Accessibility extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-accessibility';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Section Accessibility';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates comment form and display section accessibility (WCAG 2.1 AA)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Accessibility' );
	}
}
