<?php
/**
 * Theme Comment Form Support Treatment
 *
 * Detects issues with theme's comment form implementation and customization options.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Comment Form Support Treatment Class
 *
 * Checks for proper theme comment form implementation, including template
 * customization, accessibility features, and styling support.
 *
 * @since 0.6093.1200
 */
class Treatment_Theme_Comment_Form_Support extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-comment-form-support';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Comment Form Support';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme properly supports comment forms';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Comment_Form_Support' );
	}
}
