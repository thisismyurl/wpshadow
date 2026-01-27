<?php
/**
 * Admin Missing Title Format Diagnostic
 *
 * Checks if admin pages have proper <title> format with page name + site name.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Missing Title Format Diagnostic Class
 *
 * Validates that WordPress admin pages have proper <title> tags
 * with correct format: "Page Name ‹ Site Name — WordPress"
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Missing_Title_Format extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-missing-title-format';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Page Title Format';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if admin pages have proper title format with page name and site name';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only run in admin context.
		if ( ! is_admin() ) {
			return null;
		}

		// Start output buffering to capture admin page output.
		ob_start();
		
		// Hook into admin_head to capture the title.
		$title_found = false;
		$title_content = '';
		
		add_action( 'admin_head', function() use ( &$title_found, &$title_content ) {
			// Get the current page title from wp_get_document_title().
			if ( function_exists( 'wp_get_document_title' ) ) {
				$title_content = wp_get_document_title();
				$title_found = true;
			}
		}, 1 );

		// Check if title filter is being used properly.
		$has_title_filter = has_filter( 'document_title_parts' ) || has_filter( 'admin_title' );

		if ( ! $has_title_filter ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __(
					'Admin pages should have properly formatted page titles that include both the page name and site name. This improves browser tab identification and SEO. Ensure the document_title_parts or admin_title filter is not being removed.',
					'wpshadow'
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-missing-title-format',
			);
		}

		// If we're in an actual admin page request, check the title format.
		if ( $title_found && ! empty( $title_content ) ) {
			$site_name = get_bloginfo( 'name' );
			
			// Check if title contains site name.
			if ( false === strpos( $title_content, $site_name ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: 1: Current title, 2: Site name */
						__( 'Admin page title "%1$s" does not include the site name "%2$s". Proper format should be: "Page Name ‹ Site Name — WordPress"', 'wpshadow' ),
						esc_html( $title_content ),
						esc_html( $site_name )
					),
					'severity'     => 'medium',
					'threat_level' => 35,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/admin-missing-title-format',
				);
			}
		}

		ob_end_clean();

		return null; // Title format is acceptable.
	}
}
