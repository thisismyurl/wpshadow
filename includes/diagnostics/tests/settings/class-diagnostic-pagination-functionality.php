<?php
/**
 * Pagination Functionality Diagnostic
 *
 * Validates that pagination is properly implemented in the theme and
 * that archive pages work correctly with appropriate navigation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pagination Functionality Diagnostic Class
 *
 * Checks pagination implementation in theme templates.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Pagination_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pagination-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Pagination Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates theme pagination implementation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues       = array();
		$template_dir = get_template_directory();

		// Check key archive templates for pagination.
		$archive_templates = array(
			'archive.php',
			'index.php',
			'category.php',
			'tag.php',
			'author.php',
			'search.php',
		);

		$pagination_functions = array(
			'the_posts_pagination',
			'paginate_links',
			'posts_nav_link',
			'next_posts_link',
			'previous_posts_link',
		);

		$templates_without_pagination = array();

		foreach ( $archive_templates as $template ) {
			$template_path = $template_dir . '/' . $template;
			if ( file_exists( $template_path ) ) {
				$content = file_get_contents( $template_path );

				$has_pagination = false;
				foreach ( $pagination_functions as $func ) {
					if ( false !== stripos( $content, $func ) ) {
						$has_pagination = true;
						break;
					}
				}

				if ( ! $has_pagination ) {
					$templates_without_pagination[] = $template;
				}
			}
		}

		if ( ! empty( $templates_without_pagination ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of templates */
				__( 'Templates missing pagination: %s', 'wpshadow' ),
				implode( ', ', $templates_without_pagination )
			);
		}

		// Check posts_per_page setting.
		$posts_per_page = get_option( 'posts_per_page', 10 );
		if ( $posts_per_page < 1 ) {
			$issues[] = __( 'Posts per page is set to 0 or negative (pagination will not work)', 'wpshadow' );
		} elseif ( $posts_per_page > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: posts per page count */
				__( 'Posts per page set to %d (may cause performance issues)', 'wpshadow' ),
				$posts_per_page
			);
		}

		// Check for pagination plugin conflicts.
		$pagination_plugins = array(
			'wp-pagenavi/wp-pagenavi.php',
			'wp-paginate/wp-paginate.php',
		);

		$active_pagination_plugins = array();
		foreach ( $pagination_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_pagination_plugins[] = basename( dirname( $plugin ) );
			}
		}

		if ( count( $active_pagination_plugins ) > 1 ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of plugins */
				__( 'Multiple pagination plugins active: %s (may cause conflicts)', 'wpshadow' ),
				implode( ', ', $active_pagination_plugins )
			);
		}

		// Check for broken pagination due to custom queries.
		global $wpdb;
		$templates_with_custom_query = array();

		foreach ( array_merge( $archive_templates, array( 'home.php', 'front-page.php' ) ) as $template ) {
			$template_path = $template_dir . '/' . $template;
			if ( file_exists( $template_path ) ) {
				$content = file_get_contents( $template_path );
				if ( preg_match( '/new\s+WP_Query\([^)]*["\']paged["\']/i', $content ) ) {
					// Has custom query with paged - good.
				} elseif ( preg_match( '/new\s+WP_Query/i', $content ) ) {
					$templates_with_custom_query[] = $template;
				}
			}
		}

		if ( ! empty( $templates_with_custom_query ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of templates */
				__( 'Custom WP_Query without paged parameter in: %s (pagination may not work)', 'wpshadow' ),
				implode( ', ', $templates_with_custom_query )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of pagination issues */
					__( 'Found %d pagination implementation issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'posts_per_page' => $posts_per_page,
					'recommendation' => __( 'Ensure all archive templates include pagination functions and custom queries use the paged parameter.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
