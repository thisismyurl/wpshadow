<?php
/**
 * Accept-Language Header Abuse Not Prevented Diagnostic
 *
 * Checks Accept-Language abuse.
 *
 * @package    WPShadow
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
 * Diagnostic_Accept_Language_Header_Abuse_Not_Prevented Class
 *
 * Performs diagnostic check for Accept Language Header Abuse Not Prevented.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Accept_Language_Header_Abuse_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'accept-language-header-abuse-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Accept-Language Header Abuse Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks Accept-Language abuse';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'validate_accept_language' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Accept-Language headers are not validated. Checking language codes helps prevent unexpected header values from affecting site behavior.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/accept-language-header-abuse-not-prevented?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
