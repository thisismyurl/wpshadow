<?php
/**
 * Unique Selling Proposition Diagnostic
 *
 * Checks whether a clear unique selling proposition (USP) is visible.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Unique Selling Proposition Diagnostic Class
 *
 * Verifies that a clear value statement appears on the homepage.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Unique_Selling_Proposition extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'unique-selling-proposition';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Clear Unique Selling Proposition (USP)';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if a clear value statement is visible on the homepage';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$front_page_id = (int) get_option( 'page_on_front' );
		if ( 0 === $front_page_id ) {
			$issues[] = __( 'No static homepage detected to evaluate a clear value statement', 'wpshadow' );
		} else {
			$front_page = get_post( $front_page_id );
			$content    = $front_page ? wp_strip_all_tags( $front_page->post_content ) : '';
			$title      = $front_page ? wp_strip_all_tags( $front_page->post_title ) : '';

			$stats['homepage_title']  = $title ? $title : 'unknown';
			$stats['content_length']  = strlen( $content );

			$keywords = array(
				'why choose',
				'only',
				'unique',
				'different',
				'best',
				'guarantee',
				'promise',
			);

			$found_keyword = false;
			$lower_content = strtolower( $content . ' ' . $title );
			foreach ( $keywords as $keyword ) {
				if ( false !== strpos( $lower_content, $keyword ) ) {
					$found_keyword = true;
					break;
				}
			}

			if ( strlen( $content ) < 200 || ! $found_keyword ) {
				$issues[] = __( 'Homepage does not highlight a clear and specific value statement', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'A clear value statement helps visitors understand why they should choose you. Without it, people often compare only on price.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/unique-selling-proposition?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
