<?php
/**
 * Comment Spam Prevention Not Configured Treatment
 *
 * Checks if spam prevention is configured.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Spam Prevention Not Configured Treatment Class
 *
 * Detects missing spam prevention.
 *
 * @since 1.6030.2352
 */
class Treatment_Comment_Spam_Prevention_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-spam-prevention-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Spam Prevention Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if spam prevention is configured';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for spam prevention plugin
		if ( ! is_plugin_active( 'akismet/akismet.php' ) && ! is_plugin_active( 'antispam-bee/antispam-bee.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Comment spam prevention is not configured. Enable Akismet or another spam filter to automatically block spam comments.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-spam-prevention-not-configured',
			);
		}

		return null;
	}
}
