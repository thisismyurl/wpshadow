<?php
/**
 * Comment Link Count Limits Diagnostic
 *
 * Checks if comment link count is properly limited to prevent spam.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26031.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Link Count Diagnostic Class
 *
 * @since 1.26031.1300
 */
class Diagnostic_Comment_Link_Count_Limits extends Diagnostic_Base {

	protected static $slug = 'comment-link-count-limits';
	protected static $title = 'Comment Link Count Limits';
	protected static $description = 'Checks if comment link count is limited';
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26031.1300
	 * @return array|null
	 */
	public static function check() {
		$moderation_keys = get_option( 'comment_max_links', 2 );

		if ( $moderation_keys > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: maximum links allowed */
					__( 'Comment link limit is set to %d - recommended: 2 or fewer to prevent spam', 'wpshadow' ),
					$moderation_keys
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/comment-link-count-limits',
			);
		}

		return null;
	}
}
