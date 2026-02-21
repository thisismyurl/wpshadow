<?php
/**
 * Comment Template Functionality Treatment
 *
 * Validates that comment templates are properly implemented with
 * moderation controls, spam protection, and accessibility features.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1230
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Template Functionality Treatment Class
 *
 * Checks comment template implementation and settings.
 *
 * @since 1.6032.1230
 */
class Treatment_Comment_Template_Functionality extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-template-functionality';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Template Functionality';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates comment template implementation';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Template_Functionality' );
	}
}
