<?php
/**
 * 404 Error Page Functionality Diagnostic
 *
 * Validates that the 404 error page is properly configured with appropriate
 * template, search functionality, and helpful content for users.
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
 * 404 Error Page Functionality Diagnostic Class
 *
 * Checks 404 template implementation and configuration.
 *
 * @since 1.6093.1200
 */
class Diagnostic_404_Error_Page_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = '404-error-page-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = '404 Error Page Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates 404 error page configuration';

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

		// Check for 404.php template.
		$error_404_file = $template_dir . '/404.php';
		if ( ! file_exists( $error_404_file ) ) {
			$issues[] = __( 'Your site is missing a dedicated "Page Not Found" template (404.php), so visitors may see a generic page when a link is broken.', 'wpshadow' );
		} else {
			$content = file_get_contents( $error_404_file );

			// Check for search form.
			if ( false === stripos( $content, 'get_search_form' ) && false === stripos( $content, '<form' ) && false === stripos( $content, 'role="search"' ) ) {
				$issues[] = __( 'Your "Page Not Found" screen does not include a search box, so visitors have no easy way to find the page they wanted.', 'wpshadow' );
			}

			// Check for helpful navigation.
			$helpful_elements = array(
				'wp_nav_menu'      => false,
				'get_recent_posts' => false,
				'wp_list_pages'    => false,
				'get_archives'     => false,
			);

			foreach ( $helpful_elements as $element => $found ) {
				if ( false !== stripos( $content, $element ) ) {
					$helpful_elements[ $element ] = true;
				}
			}

			if ( ! in_array( true, $helpful_elements, true ) ) {
				$issues[] = __( 'Your "Page Not Found" screen does not guide visitors to helpful next steps (such as menu links, recent posts, or archives).', 'wpshadow' );
			}

			// Check content length (should be substantial).
			if ( strlen( $content ) < 500 ) {
				$issues[] = __( 'Your "Page Not Found" screen is very short and may feel like a dead end. Adding a friendly message and useful links can keep visitors engaged.', 'wpshadow' );
			}
		}

		// Check for 404 redirect plugins (can mask underlying issues).
		$redirect_plugins = array(
			'redirection/redirection.php',
			'404-solution/404solution.php',
			'404-to-301/404-to-301.php',
		);

		$has_404_redirect = false;
		foreach ( $redirect_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_404_redirect = true;
				break;
			}
		}

		if ( $has_404_redirect ) {
			$issues[] = __( 'A 404 redirect plugin is active. This can help, but it can also hide broken links that should be fixed directly.', 'wpshadow' );
		}

		// Check recent 404 errors from option-based metrics.
		$recent_404s = (int) get_option( 'wpshadow_404_count_7d', 0 );

		if ( $recent_404s > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of 404 errors */
				__( 'Your site had %d "Page Not Found" visits in the last 7 days, which may mean visitors and search engines are hitting broken links.', 'wpshadow' ),
				$recent_404s
			);
		}

		if ( ! empty( $issues ) ) {
			$primary_issue = (string) $issues[0];
			$remaining     = count( $issues ) - 1;

			if ( 0 < $remaining ) {
				$description = sprintf(
					/* translators: 1: specific issue, 2: number of additional issues */
					__( '%1$s (plus %2$d additional issue(s)).', 'wpshadow' ),
					$primary_issue,
					$remaining
				);
			} else {
				$description = $primary_issue;
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'recommendation' => __( 'Ensure 404.php includes search form, helpful navigation, and user-friendly content.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
