<?php
/**
 * Diagnostic: SameSite Cookie Attribute
 *
 * Checks SameSite attribute configuration for cookies.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Samesite_Cookie_Attribute
 *
 * Tests SameSite cookie configuration via PHP ini settings.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Samesite_Cookie_Attribute extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'samesite-cookie-attribute';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'SameSite Cookie Attribute';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks SameSite cookie attribute configuration';

	/**
	 * Check SameSite cookie ini settings.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$samesite = ini_get( 'session.cookie_samesite' );

		if ( empty( $samesite ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'session.cookie_samesite is not set. SameSite helps mitigate CSRF by restricting cross-site cookie sending. Consider setting to Lax or Strict.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/samesite_cookie_attribute',
				'meta'        => array(
					'session_cookie_samesite' => '',
				),
			);
		}

		return null;
	}
}
