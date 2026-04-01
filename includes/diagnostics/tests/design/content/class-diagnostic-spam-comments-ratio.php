<?php
/**
 * Spam Comments Ratio Diagnostic
 *
 * Checks whether spam comments significantly exceed approved comments.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Spam_Comments_Ratio Class
 *
 * Evaluates spam vs approved comments to detect moderation issues.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Spam_Comments_Ratio extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'spam-comments-ratio';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Spam Comments Ratio';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether spam comments overwhelm legitimate comments';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
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
				'kb_link'      => 'https://wpshadow.com/kb/spam-comments-ratio?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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
				'kb_link'      => 'https://wpshadow.com/kb/spam-comments-ratio?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'spam'     => $spam,
					'approved' => $approved,
				),
			);
		}

		return null;
	}
}