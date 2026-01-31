<?php
/**
 * Akismet Anti Spam Api Key Diagnostic
 *
 * Akismet Anti Spam Api Key issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1443.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Akismet Anti Spam Api Key Diagnostic Class
 *
 * @since 1.1443.0000
 */
class Diagnostic_AkismetAntiSpamApiKey extends Diagnostic_Base {

	protected static $slug = 'akismet-anti-spam-api-key';
	protected static $title = 'Akismet Anti Spam Api Key';
	protected static $description = 'Akismet Anti Spam Api Key issue found';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'AKISMET_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: API key configured.
		$api_key = \Akismet::get_api_key();
		if ( empty( $api_key ) ) {
			$issues[] = 'Akismet API key not configured';
		}

		// Check 2: API key is valid.
		if ( ! empty( $api_key ) ) {
			$key_status = \Akismet::verify_key( $api_key );
			if ( 'invalid' === $key_status ) {
				$issues[] = 'Akismet API key is invalid';
			} elseif ( 'failed' === $key_status ) {
				$issues[] = 'Unable to verify Akismet API key (connectivity issue)';
			}
		}

		// Check 3: Akismet is enabled for comments.
		$comment_moderation = get_option( 'akismet_strictness', 0 );
		if ( empty( $comment_moderation ) ) {
			$issues[] = 'Akismet comment filtering not configured';
		}

		// Check 4: Auto-discard spam enabled.
		$auto_discard = get_option( 'akismet_discard_month', 'false' );
		if ( 'false' === $auto_discard ) {
			$issues[] = 'Akismet not set to auto-discard spam (spam accumulates)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 60 + ( count( $issues ) * 6 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Akismet configuration issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/akismet-anti-spam-api-key',
			);
		}

		return null;
	}
}
