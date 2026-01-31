<?php
/**
 * Blog Comment Engagement Not Monitored Diagnostic
 *
 * Checks if comment engagement is monitored.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blog Comment Engagement Not Monitored Diagnostic Class
 *
 * Detects missing comment engagement tracking.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Blog_Comment_Engagement_Not_Monitored extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'blog-comment-engagement-not-monitored';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Blog Comment Engagement Not Monitored';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if comment engagement is monitored';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$total_comments = wp_count_comments();
		$approved        = isset( $total_comments->approved ) ? $total_comments->approved : 0;

		if ( absint( $approved ) === 0 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Blog comment engagement is not monitored. Enable and encourage comments to increase user engagement and community.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/blog-comment-engagement-not-monitored',
			);
		}

		return null;
	}
}
