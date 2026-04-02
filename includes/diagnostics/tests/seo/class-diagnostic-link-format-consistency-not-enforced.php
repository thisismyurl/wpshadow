<?php
/**
 * Link Format Consistency Not Enforced Diagnostic
 *
 * Checks if link formats are consistent.
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
 * Link Format Consistency Not Enforced Diagnostic Class
 *
 * Detects inconsistent link formats.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Link_Format_Consistency_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'link-format-consistency-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Link Format Consistency Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if link formats are consistent';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for link format enforcement
		if ( ! get_option( 'enforce_link_format' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Link format consistency is not enforced. Use consistent link formats throughout your site for better SEO and user experience.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/link-format-consistency-not-enforced',
			);
		}

		return null;
	}
}
