<?php
/**
 * Local By Flywheel Live Links Diagnostic
 *
 * Local By Flywheel Live Links issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1068.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Local By Flywheel Live Links Diagnostic Class
 *
 * @since 1.1068.0000
 */
class Diagnostic_LocalByFlywheelLiveLinks extends Diagnostic_Base {

	protected static $slug = 'local-by-flywheel-live-links';
	protected static $title = 'Local By Flywheel Live Links';
	protected static $description = 'Local By Flywheel Live Links issue detected';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();
		
		// Check 1: Live Links feature enabled
		$live_links = get_option( 'local_flywheel_live_links_enabled', false );
		if ( ! $live_links ) {
			$issues[] = 'Live Links feature disabled';
		}
		
		// Check 2: SSL certificate for Live Links
		$ssl_certificate = get_option( 'local_flywheel_live_links_ssl', false );
		if ( ! $ssl_certificate ) {
			$issues[] = 'SSL certificate not configured';
		}
		
		// Check 3: Link expiration monitoring
		$expiration_monitoring = get_option( 'local_flywheel_link_expiration_monitoring', false );
		if ( ! $expiration_monitoring ) {
			$issues[] = 'Link expiration not monitored';
		}
		
		// Check 4: Access logs enabled
		$access_logs = get_option( 'local_flywheel_live_links_logs', false );
		if ( ! $access_logs ) {
			$issues[] = 'Access logs disabled';
		}
		
		// Check 5: Share settings configured
		$share_settings = get_option( 'local_flywheel_share_settings', false );
		if ( ! $share_settings ) {
			$issues[] = 'Share settings not configured';
		}
		
		// Check 6: Link security enabled
		$link_security = get_option( 'local_flywheel_link_security', false );
		if ( ! $link_security ) {
			$issues[] = 'Link security disabled';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 35 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Local by Flywheel Live Links issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/local-by-flywheel-live-links',
			);
		}
		
		return null;
	}
		}
		return null;
	}
}
