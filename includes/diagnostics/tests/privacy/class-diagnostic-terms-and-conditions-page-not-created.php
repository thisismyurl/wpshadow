<?php
/**
 * Terms And Conditions Page Not Created Diagnostic
 *
 * Checks if T&C page exists.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2351
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Terms And Conditions Page Not Created Diagnostic Class
 *
 * Detects missing T&C page.
 *
 * @since 1.2601.2351
 */
class Diagnostic_Terms_And_Conditions_Page_Not_Created extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'terms-and-conditions-page-not-created';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Terms And Conditions Page Not Created';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if T&C page exists';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2351
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check for terms page in menu or pages
		$terms_page = $wpdb->get_var(
			"SELECT ID FROM {$wpdb->posts}
			 WHERE post_type IN ('page', 'post')
			 AND (post_title LIKE '%terms%' OR post_content LIKE '%terms%')
			 AND post_status = 'publish'
			 LIMIT 1"
		);

		if ( empty( $terms_page ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Terms and conditions page is not created. Create and link a T&C page for legal protection.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/terms-and-conditions-page-not-created',
			);
		}

		return null;
	}
}
