<?php
/**
 * Google Tag Manager Installation Not Verified Diagnostic
 *
 * Checks if GTM is installed.
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
 * Google Tag Manager Installation Not Verified Diagnostic Class
 *
 * Detects unverified GTM.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Google_Tag_Manager_Installation_Not_Verified extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'google-tag-manager-installation-not-verified';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Google Tag Manager Installation Not Verified';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if GTM is installed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for GTM container ID
		if ( ! get_option( 'gtm_container_id' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Google Tag Manager is not installed. Install GTM to centralize analytics tracking and manage tags without editing code.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/google-tag-manager-installation-not-verified',
			);
		}

		return null;
	}
}
