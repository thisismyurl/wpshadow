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
		$counts = wp_count_comments();
		if ( ! $counts ) {
			return null;
		}

		$approved = (int) $counts->approved;
		$spam     = (int) $counts->spam;
		$total    = $approved + $spam;

		if ( $total < 10 ) {
			return null;
		}

		if ( $spam > $approved * 3 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Spam comments significantly outnumber approved comments. Review anti-spam settings.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/spam-comments-ratio',
				'meta'         => array(
					'spam'     => $spam,
					'approved' => $approved,
				),
			);
		}

		if ( $spam > $approved ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Spam comments exceed approved comments. Consider tightening spam filters.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/spam-comments-ratio',
				'meta'         => array(
					'spam'     => $spam,
					'approved' => $approved,
				),
			);
		}

		return null;
	}
}