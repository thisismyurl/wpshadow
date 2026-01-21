<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

use WPShadow\Treatments\Treatment_Registry;

/**
 * Guardian Auto-Fix Policy Manager
 * 
 * Manages which treatments are safe to auto-apply.
 * Users explicitly approve each treatment for auto-fix.
 * Only approved, low-risk treatments are candidates.
 * 
 * Features:
 * - Safe treatment whitelist
 * - Execution time controls
 * - Rollback capabilities
 * - Audit trail
 * 
 * Philosophy: User control always. Never force fixes.
 * Opt-in auto-fix with explicit approval per treatment.
 */
class Auto_Fix_Policy_Manager {
	
	/**
	 * Get safe fixes approved by user
	 * 
	 * Returns list of treatment IDs approved for auto-execution.
	 * 
	 * @return array List of treatment IDs
	 */
	public static function get_safe_fixes(): array {
		$safe_fixes = get_option( 'wpshadow_guardian_safe_fixes', [] );
		return is_array( $safe_fixes ) ? $safe_fixes : [];
	}
	
	/**
	 * Add treatment to safe fixes list
	 * 
	 * Requires user approval. Only called from admin UI.
	 * 
	 * @param string $treatment_id Treatment to approve
	 * 
	 * @return bool Added successfully
	 */
	public static function approve_for_auto_fix( string $treatment_id ): bool {
		$treatment_id = sanitize_key( $treatment_id );
		
		// Verify treatment exists
		try {
			$treatment = Treatment_Registry::get( $treatment_id );
			if ( ! $treatment ) {
				return false;
			}
		} catch ( \Exception $e ) {
			return false;
		}
		
		// Get current list
		$safe_fixes = self::get_safe_fixes();
		
		// Add if not already approved
		if ( ! in_array( $treatment_id, $safe_fixes, true ) ) {
			$safe_fixes[] = $treatment_id;
		}
		
		// Save
		update_option( 'wpshadow_guardian_safe_fixes', $safe_fixes );
		
		// Log approval
		self::log_policy_change( 'approved', $treatment_id );
		
		return true;
	}
	
	/**
	 * Remove treatment from safe fixes
	 * 
	 * User explicitly revokes auto-fix permission.
	 * 
	 * @param string $treatment_id Treatment to revoke
	 * 
	 * @return bool Removed successfully
	 */
	public static function revoke_auto_fix( string $treatment_id ): bool {
		$treatment_id = sanitize_key( $treatment_id );
		
		$safe_fixes = self::get_safe_fixes();
		$safe_fixes = array_values( array_filter(
			$safe_fixes,
			fn( $t ) => $t !== $treatment_id
		) );
		
		update_option( 'wpshadow_guardian_safe_fixes', $safe_fixes );
		
		// Log revocation
		self::log_policy_change( 'revoked', $treatment_id );
		
		return true;
	}
	
	/**
	 * Check if treatment is approved for auto-fix
	 * 
	 * @param string $treatment_id Treatment to check
	 * 
	 * @return bool Is approved
	 */
	public static function is_approved_for_auto_fix( string $treatment_id ): bool {
		$treatment_id = sanitize_key( $treatment_id );
		return in_array( $treatment_id, self::get_safe_fixes(), true );
	}
	
	/**
	 * Get execution time for auto-fixes
	 * 
	 * Returns scheduled time (default: 2 AM).
	 * 
	 * @return string Time in HH:MM format
	 */
	public static function get_execution_time(): string {
		$time = get_option( 'wpshadow_guardian_auto_fix_time', '02:00' );
		return is_string( $time ) ? $time : '02:00';
	}
	
	/**
	 * Set execution time for auto-fixes
	 * 
	 * @param string $time Time in HH:MM format
	 * 
	 * @return bool Updated successfully
	 */
	public static function set_execution_time( string $time ): bool {
		// Validate HH:MM format
		if ( ! preg_match( '/^\d{2}:\d{2}$/', $time ) ) {
			return false;
		}
		
		update_option( 'wpshadow_guardian_auto_fix_time', $time );
		
		// Log change
		self::log_policy_change( 'time_updated', $time );
		
		return true;
	}
	
	/**
	 * Get max treatments per run
	 * 
	 * Limits number of auto-fixes in single execution.
	 * Prevents system overload.
	 * 
	 * @return int Max treatments
	 */
	public static function get_max_treatments_per_run(): int {
		$max = (int) get_option( 'wpshadow_guardian_max_fixes_per_run', 5 );
		return max( 1, min( 20, $max ) );
	}
	
	/**
	 * Set max treatments per run
	 * 
	 * @param int $max Number of treatments
	 * 
	 * @return bool Updated successfully
	 */
	public static function set_max_treatments_per_run( int $max ): bool {
		$max = max( 1, min( 20, $max ) );
		update_option( 'wpshadow_guardian_max_fixes_per_run', $max );
		return true;
	}
	
	/**
	 * Check if auto-fix should skip on failures
	 * 
	 * When true, one failure won't stop remaining fixes.
	 * When false, first failure halts execution.
	 * 
	 * @return bool Continue on error
	 */
	public static function should_continue_on_error(): bool {
		return (bool) get_option( 'wpshadow_guardian_continue_on_error', true );
	}
	
	/**
	 * Set continue-on-error policy
	 * 
	 * @param bool $continue Continue after fix failure
	 */
	public static function set_continue_on_error( bool $continue ): void {
		update_option( 'wpshadow_guardian_continue_on_error', $continue );
	}
	
	/**
	 * Get list of available treatments for auto-fix
	 * 
	 * Returns treatments marked as safe (can_auto_apply).
	 * User selects from this list which ones to enable.
	 * 
	 * @return array Available treatments with metadata
	 */
	public static function get_available_treatments(): array {
		$available = [];
		
		try {
			$all_treatments = Treatment_Registry::get_all();
			
			foreach ( $all_treatments as $treatment ) {
				// Only include treatments with auto-fix capability
				if ( ! method_exists( $treatment, 'can_auto_apply' ) ) {
					continue;
				}
				
				if ( ! $treatment::can_auto_apply() ) {
					continue;
				}
				
				$available[] = [
					'id'          => $treatment::get_id(),
					'name'        => $treatment::get_name(),
					'description' => $treatment::get_description(),
					'risk_level'  => $treatment::get_risk_level() ?? 'low',
					'approved'    => self::is_approved_for_auto_fix( $treatment::get_id() ),
				];
			}
		} catch ( \Exception $e ) {
			error_log( 'Error retrieving available treatments: ' . $e->getMessage() );
		}
		
		return $available;
	}
	
	/**
	 * Get policy summary
	 * 
	 * Returns all current auto-fix policies.
	 * 
	 * @return array Policy summary
	 */
	public static function get_policy_summary(): array {
		return [
			'auto_fix_enabled'      => (bool) get_option( 'wpshadow_guardian_auto_fix_enabled' ),
			'safe_fixes_count'      => count( self::get_safe_fixes() ),
			'execution_time'        => self::get_execution_time(),
			'max_per_run'           => self::get_max_treatments_per_run(),
			'continue_on_error'     => self::should_continue_on_error(),
			'available_treatments'  => count( self::get_available_treatments() ),
		];
	}
	
	/**
	 * Log policy changes
	 * 
	 * @param string $action Action performed
	 * @param string $data   Treatment ID or other data
	 */
	private static function log_policy_change( string $action, string $data ): void {
		$logs = get_option( 'wpshadow_guardian_policy_log', [] );
		
		$logs[] = [
			'timestamp' => current_time( 'mysql' ),
			'action'    => sanitize_key( $action ),
			'data'      => sanitize_text_field( $data ),
			'user_id'   => get_current_user_id(),
		];
		
		// Keep last 100 entries
		$logs = array_slice( $logs, -100 );
		
		update_option( 'wpshadow_guardian_policy_log', $logs );
	}
	
	/**
	 * Get policy change log
	 * 
	 * @param int $limit Number of entries
	 * 
	 * @return array Recent policy changes
	 */
	public static function get_policy_log( int $limit = 50 ): array {
		$logs = get_option( 'wpshadow_guardian_policy_log', [] );
		return array_slice( $logs, -$limit );
	}
}
