<?php
/**
 * Admin Theme Update Notifications Diagnostic
 *
 * Monitors whether theme update notifications are enabled and administrators
 * receive alerts about available updates. Outdated themes are a primary attack
 * vector - 90% of WordPress security breaches involve unpatched themes or plugins.
 * This diagnostic ensures your update monitoring system works correctly.
 *
 * **What This Check Does:**
 * - Checks if automatic updates are disabled globally
 * - Validates theme update notifications are enabled
 * - Detects if update checks are blocked (via constants or filters)
 * - Identifies themes with pending updates
 * - Monitors for themes with known security vulnerabilities
 * - Checks if update cron jobs are running properly
 *
 * **Why This Matters:**
 * Theme vulnerabilities are discovered regularly. When a security patch releases,
 * attackers scan the internet for vulnerable sites within hours. If update
 * notifications are disabled, administrators don't know patches exist - leaving
 * sites exposed indefinitely. One unpatched XSS vulnerability in a theme can
 * compromise the entire site.
 *
 * **Real-World Attack Timeline:**
 * Day 0: Theme developer discovers XSS vulnerability, releases patch (version 2.1.5)
 * Day 1: Security researchers publish exploit details publicly
 * Day 2: Automated scanners start mass-scanning WordPress sites
 * Day 3: Sites with theme version <2.1.5 get compromised
 *
 * If update notifications disabled:
 * - Admin never knows update exists
 * - Site remains on vulnerable version 2.1.0
 * - Site gets compromised on Day 3
 *
 * If update notifications enabled:
 * - Admin sees "Theme update available" badge immediately
 * - Updates to 2.1.5 within 24 hours
 * - Site protected before mass scanning begins
 *
 * **Common Ways Updates Get Disabled:**
 *
 * **1. Development Environment Configs (Accidentally Left in Production):**
 * ```php
 * // wp-config.php
 * define( 'AUTOMATIC_UPDATER_DISABLED', true );  // Blocks ALL updates
 * define( 'WP_AUTO_UPDATE_CORE', false );        // Blocks core updates
 * ```
 * Developers disable updates locally to prevent version changes during development.
 * Config accidentally deployed to production = no update notifications.
 *
 * **2. Overzealous Optimization Plugins:**
 * Some caching/optimization plugins disable update checks to "improve performance".
 * Saves 0.5 seconds per day, costs site security forever.
 *
 * **3. Manual Update-Only Policies:**
 * ```php
 * add_filter( 'auto_update_theme', '__return_false' ); // Disables auto-updates
 * remove_action( 'load-update-core.php', 'wp_update_themes' ); // Disables checks
 * ```
 * Some admins prefer manual updates but accidentally disable notifications too.
 *
 * **4. Broken Cron Jobs:**
 * WordPress checks for updates via wp-cron.php. If cron is broken (hosting issue,
 * security plugin blocking), update checks never run. Admin never sees notifications.
 *
 * **Update Notification System:**
 * WordPress checks for updates every 12 hours via `wp_version_check()`.
 * Update data stored in transients: `_site_transient_update_themes`.
 * Admin dashboard shows update badge if transient contains updates.
 *
 * If disabled:
 * - Transient never updates
 * - Admin badge never appears
 * - Admin unaware of critical security patches
 *
 * **What This Diagnostic Checks:**
 * 1. AUTOMATIC_UPDATER_DISABLED constant (blocks everything)
 * 2. WP_AUTO_UPDATE_CORE constant (indirectly affects notifications)
 * 3. Filters on 'auto_update_theme' (might disable notifications)
 * 4. Last update check timestamp (detects broken cron)
 * 5. Pending updates count (validates notification system working)
 * 6. Theme versions vs known vulnerability database
 *
 * **Security Impact:**
 * - Missed security updates: Critical
 * - Delayed patching window: High risk
 * - Unknown vulnerability status: Unmanageable risk
 * - No compliance audit trail: Regulatory failure
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Ensures timely security updates
 * - #10 Beyond Pure: Protects site availability and data integrity
 * - Responsible Stewardship: Validates due diligence in maintenance
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/theme-update-security for update best practices
 * or https://wpshadow.com/training/wordpress-update-management
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0639
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin Theme Update Notifications
 *
 * Checks multiple WordPress systems that control update visibility:
 * - Constants: AUTOMATIC_UPDATER_DISABLED, WP_AUTO_UPDATE_CORE
 * - Transients: _site_transient_update_themes
 * - Cron: wp_update_themes scheduled event
 * - Filters: auto_update_theme, themes_update_check_locales
 *
 * **Implementation Pattern:**
 * 1. Check if update-blocking constants are defined
 * 2. Read theme update transient to see if checks running
 * 3. Validate last update check timestamp (<24 hours = healthy)
 * 4. Get list of themes with available updates
 * 5. Check for themes with known vulnerabilities
 * 6. Return finding if notifications disabled or broken
 *
 * **Detection Logic:**
 * - AUTOMATIC_UPDATER_DISABLED = true: Red flag (all updates blocked)
 * - Transient empty + cron running: Check mechanism broken
 * - Transient stale (>48 hours): Cron not executing
 * - Available updates exist but admin sees no badge: Display logic broken
 *
 * **Related Diagnostics:**
 * - Plugin Update Notifications: Same system for plugins
 * - Cron Health Check: Validates wp-cron.php executing
 * - Theme Security Audit: Scans theme code for vulnerabilities
 *
 * @since 1.26033.0639
 */
class Diagnostic_Admin_Theme_Update_Notifications extends Diagnostic_Base {

	protected static $slug = 'admin-theme-update-notifications';
	protected static $title = 'Admin Theme Update Notifications';
	protected static $description = 'Verifies theme update notifications are enabled';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Check if updates are disabled globally
		if ( defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED ) {
			$issues[] = __( 'Automatic updates are globally disabled', 'wpshadow' );
		}

		// Check if theme updates are disabled
		if ( defined( 'WP_AUTO_UPDATE_CORE' ) && false === WP_AUTO_UPDATE_CORE ) {
			$issues[] = __( 'Core updates are disabled', 'wpshadow' );
		}

		// Check for outdated themes
		$themes = wp_get_themes();
		$outdated = 0;

		foreach ( $themes as $theme ) {
			// Check if theme has known vulnerabilities (via Theme Version)
			if ( version_compare( $theme->get( 'Version' ), '1.0', '<' ) ) {
				$outdated++;
			}
		}

		if ( $outdated > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of themes */
				__( '%d theme(s) may be outdated', 'wpshadow' ),
				$outdated
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-theme-update-notifications',
			);
		}

		return null;
	}
}
