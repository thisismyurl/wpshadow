<?php
/**
 * No Internationalized Domain Name Support Diagnostic
 *
 * Detects when internationalized domains are not supported,
 * limiting accessibility for non-English language users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Localization
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Internationalized Domain Name Support
 *
 * Checks whether internationalized domain names
 * are supported for global accessibility.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Internationalized_Domain_Name_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-internationalized-domain-name-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Internationalized Domain Name Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether IDN domains are supported';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'localization';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if domain is internationalized
		$domain = wp_parse_url( home_url(), PHP_URL_HOST );
		
		// Internationalized domains contain non-ASCII characters
		if ( preg_match( '/[^\x00-\x7F]/', $domain ) ) {
			// Already internationalized, check if WordPress supports it
			if ( defined( 'ICONV_IMPL' ) && ICONV_IMPL === 'libiconv' ) {
				return null; // Support exists
			}

			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Your internationalized domain may not be fully supported. IDN domains use non-Latin characters (例え.jp for Japanese, münchen.de for German). Benefits: users can type domain in their own script, familiarity for local markets, SEO advantages in local regions. Requires: proper server configuration (ICONV support) and WordPress setup. Check with hosting provider for IDN support.',
					'wpshadow'
				),
				'severity'      => 'low',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'International Accessibility',
					'potential_gain' => 'Enable non-Latin-alphabet users to type domain in native script',
					'roi_explanation' => 'IDN domains improve accessibility for users in non-Latin markets.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/internationalized-domain-name-support',
			);
		}

		return null;
	}
}
