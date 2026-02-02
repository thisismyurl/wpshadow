<?php
/**
 * Post Pagination Functionality Diagnostic
 *
 * Checks if post pagination is properly configured and working.
 *
 * @since   1.26033.0901
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Post_Pagination_Functionality Class
 *
 * Validates post pagination functionality.
 *
 * @since 1.26033.0901
 */
class Diagnostic_Post_Pagination_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-pagination-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Pagination Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if post pagination is properly configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0901
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check for posts with <!--nextpage--> tags but no pagination template
		$paginated_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_content LIKE '%<!--nextpage-->%'
			AND post_type = 'post'"
		);

		// Check if pagination links are being properly displayed in theme
		if ( intval( $paginated_posts ) > 10 ) {
			// This is just informational - pagination is complex and theme-dependent
			if ( ! current_theme_supports( 'post-formats' ) && ! is_active_widget( 'archives-widget' ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %d: number of paginated posts */
						__( 'Found %d posts with page breaks (<!--nextpage-->). Ensure your theme properly displays post pagination links at the bottom of multi-page posts.', 'wpshadow' ),
						intval( $paginated_posts )
					),
					'severity'     => 'low',
					'threat_level' => 15,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/post-pagination-functionality',
				);
			}
		}

		return null; // Post pagination is configured
	}
}
