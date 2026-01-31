<?php
/**
 * Akismet Anti Spam Comment Checking Diagnostic
 *
 * Akismet Anti Spam Comment Checking issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1445.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Akismet Anti Spam Comment Checking Diagnostic Class
 *
 * @since 1.1445.0000
 */
class Diagnostic_AkismetAntiSpamCommentChecking extends Diagnostic_Base {

	protected static $slug = 'akismet-anti-spam-comment-checking';
	protected static $title = 'Akismet Anti Spam Comment Checking';
	protected static $description = 'Akismet Anti Spam Comment Checking issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'AKISMET_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: API key configured
		$api_key = get_option( 'wordpress_api_key' );
		if ( empty( $api_key ) ) {
			$issues[] = __( 'Akismet API key not configured (spam protection disabled)', 'wpshadow' );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Akismet API key not configured', 'wpshadow' ),
				'severity'    => 80,
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/akismet-anti-spam-comment-checking',
			);
		}
		
		// Check 2: Comment checking enabled
		$check_comments = get_option( 'akismet_check_comments', true );
		if ( ! $check_comments ) {
			$issues[] = __( 'Comment spam checking disabled', 'wpshadow' );
		}
		
		// Check 3: Trackback/pingback checking
		$check_trackbacks = get_option( 'akismet_check_trackbacks', true );
		if ( ! $check_trackbacks ) {
			$issues[] = __( 'Trackback/pingback spam checking disabled', 'wpshadow' );
		}
		
		// Check 4: Contact form protection
		$check_forms = get_option( 'akismet_check_contact_forms', false );
		if ( ! $check_forms && ( function_exists( 'wpcf7' ) || class_exists( 'GFForms' ) ) ) {
			$issues[] = __( 'Contact form spam checking not enabled (forms detected)', 'wpshadow' );
		}
		
		// Check 5: Discard spam option
		$discard_spam = get_option( 'akismet_discard_month', 'false' );
		if ( 'false' === $discard_spam ) {
			$issues[] = __( 'Worst spam not auto-discarded (queue bloat)', 'wpshadow' );
		}
		
		// Check 6: Recheck queue option
		$can_recheck = get_option( 'akismet_show_user_comments_approved', false );
		if ( ! $can_recheck ) {
			$issues[] = __( 'Cannot recheck approved comments (missed spam)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of spam checking issues */
				__( 'Akismet comment checking has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/akismet-anti-spam-comment-checking',
		);
	}
}
