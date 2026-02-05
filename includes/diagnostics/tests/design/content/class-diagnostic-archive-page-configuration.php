<?php
/**
 * Archive Page Configuration Diagnostic
 *
 * Verifies archive page settings are properly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1900
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Archive Page Configuration Diagnostic Class
 *
 * Checks archive page settings for performance and SEO.
 *
 * @since 1.6032.1900
 */
class Diagnostic_Archive_Page_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'archive-page-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Archive Page Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies archive page settings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'reading';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check posts per page on archives.
		$posts_per_page = (int) get_option( 'posts_per_page', 10 );
		if ( $posts_per_page > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: posts per page */
				__( 'Archive pages showing %d posts - may cause performance issues', 'wpshadow' ),
				$posts_per_page
			);
		} elseif ( $posts_per_page < 5 ) {
			$issues[] = sprintf(
				/* translators: %d: posts per page */
				__( 'Archive pages showing only %d posts - excessive pagination', 'wpshadow' ),
				$posts_per_page
			);
		}

		// Check if date archives are enabled.
		if ( get_query_var( 'year' ) || get_query_var( 'monthnum' ) ) {
			// Date archives enabled.
			// Check if they have content.
			global $wpdb;
			$empty_month_archives = $wpdb->get_var(
				"SELECT COUNT(DISTINCT MONTH(post_date)) FROM {$wpdb->posts}
				WHERE post_status = 'publish' AND post_type = 'post'
				GROUP BY YEAR(post_date)"
			);
		}

		// Check if category/tag archives are enabled.
		$category_base = get_option( 'category_base', 'category' );
		$tag_base = get_option( 'tag_base', 'tag' );

		if ( empty( $category_base ) && empty( $tag_base ) ) {
			$issues[] = __( 'Category and tag archives may not be properly accessible', 'wpshadow' );
		}

		// Check if author archives are enabled.
		$show_on_front = get_option( 'show_on_front', 'posts' );
		if ( $show_on_front === 'page' ) {
			$posts_page = get_option( 'page_for_posts' );
			if ( ! $posts_page ) {
				$issues[] = __( 'Static front page configured but no posts page - archives may not display', 'wpshadow' );
			}
		}

		// Check if search is enabled.
		// WordPress always enables search by default.

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/archive-page-configuration',
			);
		}

		return null;
	}
}
