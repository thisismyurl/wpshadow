<?php
/**
 * Suspicious Cron Jobs Detection Diagnostic
 *
 * Audits WordPress cron jobs to find orphaned or malicious cron entries
 * from deactivated/deleted plugins that could be executing hidden tasks.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Suspicious_Cron_Jobs Class
 *
 * Detects cron jobs registered by deactivated or deleted plugins
 * that may be malicious or abandoned.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Suspicious_Cron_Jobs extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'suspicious-cron-jobs';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Suspicious or Orphaned Cron Jobs';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects cron jobs from deleted or deactivated plugins';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Known malicious cron job patterns
	 *
	 * @var array
	 */
	const MALICIOUS_PATTERNS = array(
		'eval'          => 'PHP code evaluation',
		'exec'          => 'System command execution',
		'system'        => 'System command execution',
		'passthru'      => 'Command passthrough',
		'shell_exec'    => 'Shell command execution',
		'create_function' => 'Dynamic function creation',
		'call_user_func' => 'User function callback (often abused)',
		'base64_decode' => 'Base64 decoding (obfuscation)',
		'gzinflate'     => 'Gzip decompression (obfuscation)',
		'gzuncompress'  => 'Gzip uncompression (obfuscation)',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if suspicious jobs found, null otherwise.
	 */
	public static function check() {
		$suspicious_jobs = self::find_suspicious_cron_jobs();

		if ( empty( $suspicious_jobs ) ) {
			// No suspicious cron jobs detected
			return null;
		}

		$count = count( $suspicious_jobs );

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: number of suspicious cron jobs */
				__( 'Found %d suspicious or orphaned cron %s that should not be running.', 'wpshadow' ),
				$count,
				( $count === 1 ? __( 'job', 'wpshadow' ) : __( 'jobs', 'wpshadow' ) )
			),
			'severity'      => 'high',
			'threat_level'  => 75,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/suspicious-cron-jobs',
			'family'        => self::$family,
			'meta'          => array(
				'suspicious_count'     => $count,
				'suspicious_jobs'      => array_slice( $suspicious_jobs, 0, 10 ), // Show first 10
				'potential_risks'      => array(
					__( 'Hidden malware executing regularly' ),
					__( 'Backdoor tasks running undetected' ),
					__( 'Resource drain from abandoned cron jobs' ),
					__( 'Data exfiltration to attacker servers' ),
					__( 'Spam/phishing campaign distribution' ),
				),
				'immediate_actions'    => array(
					__( 'Review each suspicious cron job carefully' ),
					__( 'Search function name in code to find source' ),
					__( 'If from unknown source, clear it immediately' ),
					__( 'Scan entire site for malware' ),
					__( 'Review database tables for backdoor code' ),
				),
			),
			'details'       => array(
				'issue'            => sprintf(
					/* translators: %d: number of jobs */
					__( '%d cron jobs are not associated with active, legitimate plugins.', 'wpshadow' ),
					$count
				),
				'security_impact'  => __( 'HIGH - Unattended cron jobs could be malware executing hidden tasks, data theft, or spam distribution.', 'wpshadow' ),
				'cron_job_types'   => array(
					'Orphaned jobs' => array(
						__( 'Registered by plugin that was deleted' ),
						__( 'Registered by plugin that was deactivated' ),
						__( 'May be backup/recovery code left behind' ),
						__( 'May be intentional backdoor' ),
					),
					'Malicious jobs' => array(
						__( 'Hook names are obfuscated or encoded' ),
						__( 'Use system execution functions (exec, shell_exec)' ),
						__( 'Use base64/gzip obfuscation techniques' ),
						__( 'Registered by injected malware' ),
					),
					'Legitimate unused jobs' => array(
						__( 'Safe to remove if functionality is not needed' ),
						__( 'Check plugin documentation before removing' ),
						__( 'Only remove jobs from trusted plugins' ),
					),
				),
				'investigation_steps' => array(
					'Step 1: Identify source' => array(
						__( 'Search codebase for cron hook name' ),
						__( 'Use grep to find where hook is registered' ),
						__( 'Example: grep -r "custom_cron_hook" wp-content/' ),
					),
					'Step 2: Check plugin' => array(
						__( 'Is the plugin still installed and active?' ),
						__( 'Does the plugin exist in wp-content/plugins/?' ),
						__( 'Is the function defined in plugin code?' ),
					),
					'Step 3: Assess threat' => array(
						__( 'Does hook name look suspicious or obfuscated?' ),
						__( 'Does it use dangerous functions like exec, eval?' ),
						__( 'How often is it scheduled to run?' ),
					),
					'Step 4: Take action' => array(
						__( 'Trusted plugin: Probably safe, can leave or contact support' ),
						__( 'Unknown source: REMOVE IMMEDIATELY' ),
						__( 'Suspicious: Scan for malware before deciding' ),
					),
				),
				'removal_instructions' => __( 'Use WPShadow treatment to safely remove suspicious cron jobs. This deletes the scheduled event but does not harm the plugin (if it still exists).', 'wpshadow' ),
			),
		);
	}

	/**
	 * Find suspicious or orphaned cron jobs.
	 *
	 * @since  1.2601.2148
	 * @return array List of suspicious cron jobs.
	 */
	private static function find_suspicious_cron_jobs() {
		$suspicious     = array();
		$cron_array     = _get_cron_array();
		$active_plugins = get_option( 'active_plugins', array() );

		if ( ! is_array( $cron_array ) ) {
			return $suspicious;
		}

		// Get all defined hooks
		$all_hooks = apply_filters( 'wpshadow_check_all_hooks', true );
		global $wp_filter;

		foreach ( $cron_array as $timestamp => $crons ) {
			foreach ( $crons as $hook => $data ) {
				// Check if this is a suspicious cron
				$is_suspicious = false;
				$reason        = '';

				// 1. Check if hook is from deactivated/deleted plugin
				if ( ! self::is_hook_from_active_plugin( $hook, $active_plugins ) ) {
					$is_suspicious = true;
					$reason        = __( 'Orphaned: Not from any active plugin', 'wpshadow' );
				}

				// 2. Check for malicious patterns in hook name
				if ( self::contains_malicious_pattern( $hook ) ) {
					$is_suspicious = true;
					$reason        = __( 'Suspicious: Hook name contains dangerous function name', 'wpshadow' );
				}

				// 3. Check for obfuscated/encoded hook names
				if ( self::is_hook_obfuscated( $hook ) ) {
					$is_suspicious = true;
					$reason        = __( 'Suspicious: Hook name appears obfuscated or encoded', 'wpshadow' );
				}

				// 4. Check for high-frequency execution (< 5 minute intervals)
				if ( isset( $data[ $hook ]['schedule'] ) ) {
					$interval = wp_get_schedule( $hook );
					if ( 'every-5-min' === $interval || 'every-minute' === $interval ) {
						$is_suspicious = true;
						$reason        = __( 'Suspicious: Very frequent execution (possible resource drain)', 'wpshadow' );
					}
				}

				if ( $is_suspicious ) {
					$next_run = $timestamp > time() ? wp_date( 'Y-m-d H:i:s', $timestamp ) : __( 'Past due', 'wpshadow' );

					$suspicious[] = array(
						'hook'     => $hook,
						'schedule' => wp_get_schedule( $hook ) ?: __( 'One-time', 'wpshadow' ),
						'next_run' => $next_run,
						'reason'   => $reason,
						'timestamp' => $timestamp,
						'severity'  => self::assess_cron_threat( $hook ),
					);
				}
			}
		}

		// Sort by timestamp (next to run first)
		usort(
			$suspicious,
			function( $a, $b ) {
				return $a['timestamp'] <=> $b['timestamp'];
			}
		);

		return $suspicious;
	}

	/**
	 * Check if cron hook belongs to an active plugin.
	 *
	 * @since  1.2601.2148
	 * @param  string $hook          Cron hook name.
	 * @param  array  $active_plugins List of active plugin files.
	 * @return bool True if hook is from active plugin.
	 */
	private static function is_hook_from_active_plugin( $hook, array $active_plugins ) {
		// Known WordPress core crons - always safe
		$core_hooks = array(
			'wp_version_check',
			'wp_update_plugins',
			'wp_update_themes',
			'wp_maybe_auto_update',
			'wp_scheduled_delete',
			'wp_scheduled_auto_draft_delete',
			'wp_privacy_delete_old_export_files',
			'wp_update_user_counts',
			'wp_check_term_count',
		);

		if ( in_array( $hook, $core_hooks, true ) ) {
			return true;
		}

		// Check if hook is registered by any active plugin
		global $wp_filter;

		if ( isset( $wp_filter[ $hook ] ) && is_object( $wp_filter[ $hook ] ) ) {
			$callbacks = $wp_filter[ $hook ]->callbacks;

			if ( is_array( $callbacks ) ) {
				foreach ( $callbacks as $priority_group ) {
					foreach ( $priority_group as $callback_data ) {
						$callback = $callback_data['function'];

						// Check if callback is from active plugin
						if ( is_array( $callback ) && is_object( $callback[0] ) ) {
							$class = get_class( $callback[0] );
							if ( false !== strpos( $class, 'Plugin' ) || false !== strpos( $class, 'Module' ) ) {
								return true;
							}
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check if hook name contains malicious patterns.
	 *
	 * @since  1.2601.2148
	 * @param  string $hook Hook name.
	 * @return bool True if contains malicious pattern.
	 */
	private static function contains_malicious_pattern( $hook ) {
		foreach ( self::MALICIOUS_PATTERNS as $pattern => $risk ) {
			if ( stripos( $hook, $pattern ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if hook name appears to be obfuscated or encoded.
	 *
	 * @since  1.2601.2148
	 * @param  string $hook Hook name.
	 * @return bool True if appears obfuscated.
	 */
	private static function is_hook_obfuscated( $hook ) {
		// Base64 detection
		if ( preg_match( '/^[A-Za-z0-9+\/=]{20,}$/', $hook ) ) {
			return true;
		}

		// Long hex strings
		if ( preg_match( '/^[a-f0-9]{32,}$/', $hook ) ) {
			return true;
		}

		// Random looking with special chars/numbers
		if ( preg_match( '/^[_]+[a-z0-9]{10,}$/', $hook ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Assess threat level of cron job.
	 *
	 * @since  1.2601.2148
	 * @param  string $hook Hook name.
	 * @return string Threat level: low, medium, high, critical.
	 */
	private static function assess_cron_threat( $hook ) {
		if ( self::contains_malicious_pattern( $hook ) ) {
			return 'critical';
		}

		if ( self::is_hook_obfuscated( $hook ) ) {
			return 'high';
		}

		// Orphaned hooks from common plugins are medium risk
		return 'medium';
	}
}
