<?php
/**
 * Comment Close Automation Treatment
 *
 * Tests automatic comment closing on old posts.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Close Automation Treatment Class
 *
 * Validates that comments are automatically closed on old posts.
 *
 * @since 1.6093.1200
 */
class Treatment_Comment_Close_Automation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-close-automation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Close Automation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests automatic comment closing on old posts';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Settings';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Close_Automation' );
	}
}
