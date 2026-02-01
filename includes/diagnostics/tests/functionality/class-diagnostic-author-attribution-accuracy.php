<?php
/**
 * Author Attribution Accuracy Diagnostic
 *
 * Checks for posts with missing or invalid author attribution.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Author Attribution Accuracy Diagnostic
 *
 * Ensures posts have valid author assignments.
 *
 * @since 1.2601.2240
 */
class Diagnostic_Author_Attribution_Accuracy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'author-attribution-accuracy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Author Attribution Accuracy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for posts with missing or invalid author attribution';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$missing_authors = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_author = %d AND post_status IN ('publish','draft')",
				0
			)
		);

		if ( 0 === $missing_authors ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Posts with missing author attribution detected', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/author-attribution-accuracy',
			'details'      => array(
				'missing_authors' => $missing_authors,
			),
		);
	}
}
