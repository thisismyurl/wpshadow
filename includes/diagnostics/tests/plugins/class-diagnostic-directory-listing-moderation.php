<?php
/**
 * Directory Listing Moderation Diagnostic
 *
 * Directory moderation queue growing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.558.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Directory Listing Moderation Diagnostic Class
 *
 * @since 1.558.0000
 */
class Diagnostic_DirectoryListingModeration extends Diagnostic_Base {

	protected static $slug = 'directory-listing-moderation';
	protected static $title = 'Directory Listing Moderation';
	protected static $description = 'Directory moderation queue growing';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Auto-approve trusted users
		$auto_approve = get_option( 'wpbdp_auto_approve_trusted', false );
		if ( ! $auto_approve ) {
			$issues[] = 'Auto-approve trusted users disabled';
		}
		
		// Check 2: Moderation queue limit
		$queue_limit = get_option( 'wpbdp_moderation_queue_limit', 50 );
		if ( $queue_limit > 100 ) {
			$issues[] = 'Moderation queue limit too high';
		}
		
		// Check 3: Email notifications enabled
		$notifications = get_option( 'wpbdp_moderation_notifications', false );
		if ( ! $notifications ) {
			$issues[] = 'Moderation notifications disabled';
		}
		
		// Check 4: Spam filter enabled
		$spam_filter = get_option( 'wpbdp_spam_filter_enabled', false );
		if ( ! $spam_filter ) {
			$issues[] = 'Spam filter disabled';
		}
		
		// Check 5: Moderation roles configured
		$mod_roles = get_option( 'wpbdp_moderation_roles', array() );
		if ( empty( $mod_roles ) ) {
			$issues[] = 'No moderation roles configured';
		}
		
		// Check 6: Approval process documented
		$approval_docs = get_option( 'wpbdp_approval_process_docs', false );
		if ( ! $approval_docs ) {
			$issues[] = 'Approval process not documented';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 35 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Directory moderation issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/directory-listing-moderation',
			);
		}
		
		return null;
	}
}
