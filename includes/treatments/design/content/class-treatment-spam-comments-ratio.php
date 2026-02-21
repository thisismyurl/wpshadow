<?php
/**
 * Spam Comments Ratio Treatment
 *
 * Checks whether spam comments significantly exceed approved comments.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1410
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Spam_Comments_Ratio Class
 *
 * Evaluates spam vs approved comments to detect moderation issues.
 *
 * @since 1.6035.1410
 */
class Treatment_Spam_Comments_Ratio extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'spam-comments-ratio';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Spam Comments Ratio';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether spam comments overwhelm legitimate comments';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1410
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Spam_Comments_Ratio' );
	}
}