<?php
/**
 * Comments Per Page Configuration Treatment
 *
 * Verifies the number of comments displayed per page is optimized.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1755
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comments Per Page Configuration Treatment Class
 *
 * Checks comments per page setting for optimal balance.
 *
 * @since 1.6032.1755
 */
class Treatment_Comments_Per_Page_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comments-per-page-configuration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comments Per Page Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comments per page setting';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1755
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$page_comments = get_option( 'page_comments', 0 );
		if ( ! $page_comments ) {
			return null; // Pagination disabled, setting doesn't apply.
		}

		$comments_per_page = get_option( 'comments_per_page', 50 );

		// Check for extreme values.
		if ( $comments_per_page < 5 ) {
			$issues[] = __( 'Very low comments per page - excessive pagination', 'wpshadow' );
		} elseif ( $comments_per_page < 20 ) {
			$issues[] = __( 'Low comments per page may require frequent pagination', 'wpshadow' );
		} elseif ( $comments_per_page > 100 ) {
			$issues[] = __( 'High comments per page may slow page load time', 'wpshadow' );
		} elseif ( $comments_per_page > 200 ) {
			$issues[] = sprintf(
				/* translators: %d: comments per page */
				__( 'Extremely high comments per page (%d) will significantly impact performance', 'wpshadow' ),
				$comments_per_page
			);
		}

		// Recommended range: 20-50 comments per page.
		if ( $comments_per_page >= 20 && $comments_per_page <= 50 ) {
			// Optimal range - no issue.
			return null;
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comments-per-page-configuration',
			);
		}

		return null;
	}
}
