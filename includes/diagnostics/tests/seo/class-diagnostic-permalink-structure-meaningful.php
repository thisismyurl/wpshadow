<?php
/**
 * Permalink Structure Meaningful Diagnostic (Stub)
 *
 * TODO stub mapped to the seo gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Permalink_Structure_Meaningful Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Permalink_Structure_Meaningful extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'permalink-structure-meaningful';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Permalink Structure Meaningful';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the WordPress permalink structure is set to a human-readable format rather than the default numeric query string that harms SEO.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check permalink_structure for plain or low-context formats.
	 *
	 * TODO Fix Plan:
	 * - Use a human-readable permalink structure that supports SEO and sharing.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$permalink_structure = get_option( 'permalink_structure', '' );

		// Plain permalinks (/?p=123).
		if ( '' === $permalink_structure ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WordPress is using plain (query-string) permalinks such as /?p=123. These URLs contain no keywords and are difficult for users and search engines to interpret. Change the permalink structure under Settings → Permalinks to something more descriptive, such as "Post name" (/%postname%/).', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/permalink-structure',
				'details'      => array(
					'permalink_structure' => '',
					'format'              => 'plain',
				),
			);
		}

		// Numeric-only structure (e.g. /archives/123).
		if ( preg_match( '/^[^%]*%p%[^%]*$|\/archives\//i', $permalink_structure ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'The permalink structure uses a numeric post ID, which provides no keyword context to search engines or users. Switch to a structure that includes the post name, such as /%postname%/, for better SEO and readability.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/permalink-structure',
				'details'      => array(
					'permalink_structure' => $permalink_structure,
					'format'              => 'numeric',
				),
			);
		}

		return null;
	}
}
