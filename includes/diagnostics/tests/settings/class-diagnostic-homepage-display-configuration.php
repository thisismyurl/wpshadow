<?php
/**
 * Homepage Display Configuration Diagnostic
 *
 * Verifies that the homepage display is properly configured to show either
 * the blog feed or a static page as intended.
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
 * Homepage Display Configuration Diagnostic Class
 *
 * Ensures homepage is configured appropriately.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Homepage_Display_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'homepage-display-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Homepage Display Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies homepage display is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Homepage shows proper content (blog or static page)
	 * - Static homepage page exists if configured
	 * - Posts page exists if blog is shown
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get homepage settings.
		$show_on_front  = get_option( 'show_on_front', 'posts' );
		$page_on_front  = get_option( 'page_on_front', 0 );
		$page_for_posts = get_option( 'page_for_posts', 0 );

		if ( 'page' === $show_on_front ) {
			// Static homepage is configured.
			if ( empty( $page_on_front ) ) {
				$issues[] = __( 'Homepage is set to show a static page but no page is selected', 'wpshadow' );
			} else {
				// Check if page exists.
				$home_page = get_post( $page_on_front );
				if ( ! $home_page ) {
					$issues[] = __( 'Configured homepage page does not exist', 'wpshadow' );
				} elseif ( 'publish' !== $home_page->post_status ) {
					$issues[] = sprintf(
						/* translators: %s: post status */
						__( 'Homepage page is not published (status: %s)', 'wpshadow' ),
						$home_page->post_status
					);
				}

				// Check if posts page is set.
				if ( empty( $page_for_posts ) ) {
					$issues[] = __( 'Static homepage is enabled but no page is selected for posts; blog archive will not work', 'wpshadow' );
				} else {
					$posts_page = get_post( $page_for_posts );
					if ( ! $posts_page ) {
						$issues[] = __( 'Configured blog posts page does not exist', 'wpshadow' );
					}
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/homepage-display-configuration',
			);
		}

		return null;
	}
}
