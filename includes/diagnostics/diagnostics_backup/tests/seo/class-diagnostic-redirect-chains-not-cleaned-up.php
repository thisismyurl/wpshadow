<?php
/**
 * Redirect Chains Not Cleaned Up Diagnostic
 *
 * Checks if redirect chains exist.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redirect Chains Not Cleaned Up Diagnostic Class
 *
 * Detects redirect chains.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Redirect_Chains_Not_Cleaned_Up extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'redirect-chains-not-cleaned-up';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Redirect Chains Not Cleaned Up';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if redirect chains exist';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for redirect chain plugin
		if ( ! is_plugin_active( 'redirection/redirection.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Redirect chains have not been cleaned up. Remove redirect chains to improve page load speed and SEO performance.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/redirect-chains-not-cleaned-up',
			);
		}

		return null;
	}
}
