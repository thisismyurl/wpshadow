<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

use WPShadow\Core\KPI_Tracker;

/**
 * Guardian Compliance Checker
 * 
 * Ensures auto-fixes don't violate WordPress/plugin best practices.
 * Validates treatments before auto-execution.
 * 
 * Features:
 * - WordPress best practices validation
 * - Security compliance check
 * - Plugin/theme compatibility scan
 * - Reversibility verification
 * - Pre-fix validation
 * 
 * Philosophy: Safety first. Verify before executing.
 */
class Compliance_Checker {
	
	/**
	 * Check if treatment is safe for auto-execution
	 * 
	 * Validates:
	 * - Treatment reversibility
	 * - Security impact
	 * - Plugin compatibility
	 * - Known conflicts
	 * 
	 * @param string $treatment_class Treatment class name
	 * 
	 * @return array { compliant: bool, issues: array, warnings: array }
	 */
	public static function validate_treatment( string $treatment_class ): array {
		$result = [
			'compliant'  => true,
			'issues'     => [],
			'warnings'   => [],
			'checks'     => [],
		];
		
		// Check 1: Treatment exists and is callable
		if ( ! self::validate_treatment_exists( $treatment_class, $result ) ) {
			$result['compliant'] = false;
			return $result;
		}
		
		// Check 2: Treatment has apply/undo methods
		if ( ! self::validate_reversibility( $treatment_class, $result ) ) {
			$result['compliant'] = false;
		}
		
		// Check 3: Check for security impact
		self::validate_security_impact( $treatment_class, $result );
		
		// Check 4: Check for plugin conflicts
		self::validate_plugin_compatibility( $treatment_class, $result );
		
		// Check 5: Known conflict database
		self::validate_known_conflicts( $treatment_class, $result );
		
		// Check 6: Performance impact
		self::validate_performance( $treatment_class, $result );
		
		if ( ! empty( $result['issues'] ) ) {
			$result['compliant'] = false;
		}
		
		return $result;
	}
	
	/**
	 * Validate treatment exists and is loadable
	 * 
	 * @param string $treatment_class Class name
	 * @param array  $result Result array
	 * 
	 * @return bool Valid
	 */
	private static function validate_treatment_exists( string $treatment_class, array &$result ): bool {
		$result['checks']['exists'] = false;
		
		if ( ! class_exists( $treatment_class ) ) {
			$result['issues'][] = "Treatment class not found: {$treatment_class}";
			return false;
		}
		
		$result['checks']['exists'] = true;
		return true;
	}
	
	/**
	 * Validate treatment reversibility
	 * 
	 * @param string $treatment_class Class name
	 * @param array  $result Result array
	 * 
	 * @return bool Reversible
	 */
	private static function validate_reversibility( string $treatment_class, array &$result ): bool {
		$result['checks']['reversible'] = false;
		
		if ( ! method_exists( $treatment_class, 'apply' ) ) {
			$result['issues'][] = "Treatment missing apply() method";
			return false;
		}
		
		if ( ! method_exists( $treatment_class, 'undo' ) ) {
			$result['warnings'][] = "Treatment cannot be undone (no undo method)";
			return false;
		}
		
		$result['checks']['reversible'] = true;
		return true;
	}
	
	/**
	 * Check security impact
	 * 
	 * @param string $treatment_class Class name
	 * @param array  $result Result array
	 */
	private static function validate_security_impact( string $treatment_class, array &$result ): void {
		$result['checks']['security'] = true;
		
		// Get treatment metadata if available
		$reflection = new \ReflectionClass( $treatment_class );
		$comment = $reflection->getDocComment();
		
		// Check for security-related operations
		if ( strpos( $comment, 'Security' ) !== false ||
		     strpos( $comment, 'security' ) !== false ) {
			$result['warnings'][] = "Treatment modifies security settings - proceed with caution";
		}
		
		// Check if treatment modifies .htaccess or similar
		$source = file_get_contents( $reflection->getFileName() );
		if ( strpos( $source, '.htaccess' ) !== false ||
		     strpos( $source, 'wp-config' ) !== false ) {
			$result['issues'][] = "Treatment modifies sensitive WordPress files";
			$result['compliant'] = false;
		}
	}
	
	/**
	 * Check plugin compatibility
	 * 
	 * @param string $treatment_class Class name
	 * @param array  $result Result array
	 */
	private static function validate_plugin_compatibility( string $treatment_class, array &$result ): void {
		$result['checks']['compatibility'] = true;
		
		// Get treatment name/description
		if ( method_exists( $treatment_class, 'get_name' ) ) {
			$name = $treatment_class::get_name();
			
			// Known conflicts database
			$conflicts = self::get_known_conflicts();
			
			foreach ( $conflicts as $pattern => $issue ) {
				if ( stripos( $name, $pattern ) !== false ) {
					$result['warnings'][] = $issue;
				}
			}
		}
	}
	
	/**
	 * Check for known conflicts
	 * 
	 * @param string $treatment_class Class name
	 * @param array  $result Result array
	 */
	private static function validate_known_conflicts( string $treatment_class, array &$result ): void {
		$result['checks']['known_conflicts'] = true;
		
		$conflicts_db = get_option( 'wpshadow_treatment_conflicts', [] );
		
		if ( isset( $conflicts_db[ $treatment_class ] ) ) {
			$conflicts = $conflicts_db[ $treatment_class ];
			
			foreach ( $conflicts as $conflict ) {
				if ( $conflict['severity'] === 'critical' ) {
					$result['issues'][] = $conflict['description'];
					$result['compliant'] = false;
				} else {
					$result['warnings'][] = $conflict['description'];
				}
			}
		}
	}
	
	/**
	 * Check performance impact
	 * 
	 * @param string $treatment_class Class name
	 * @param array  $result Result array
	 */
	private static function validate_performance( string $treatment_class, array &$result ): void {
		$result['checks']['performance'] = true;
		
		// Check historical execution times
		$history = get_option( 'wpshadow_treatment_history', [] );
		
		if ( isset( $history[ $treatment_class ] ) ) {
			$executions = $history[ $treatment_class ]['executions'] ?? [];
			
			if ( ! empty( $executions ) ) {
				$avg_time = array_sum( array_column( $executions, 'duration' ) ) / count( $executions );
				
				if ( $avg_time > 30 ) {
					$result['warnings'][] = "Treatment takes ~{$avg_time}ms to execute (may timeout)";
				}
			}
		}
	}
	
	/**
	 * Get list of known conflicts
	 * 
	 * @return array Conflicts database
	 */
	private static function get_known_conflicts(): array {
		return [
			'permalink' => 'Changing permalinks may break existing links',
			'database' => 'Database modifications require backup first',
			'multisite' => 'Multisite changes affect all network sites',
		];
	}
	
	/**
	 * Record compliance check result
	 * 
	 * Stores result for audit trail.
	 * 
	 * @param string $treatment_class Treatment class
	 * @param array  $result Compliance result
	 */
	public static function record_check( string $treatment_class, array $result ): void {
		$log = get_option( 'wpshadow_compliance_log', [] );
		
		$log[] = [
			'timestamp'  => current_time( 'mysql' ),
			'treatment'  => $treatment_class,
			'compliant'  => $result['compliant'],
			'issues'     => count( $result['issues'] ),
			'warnings'   => count( $result['warnings'] ),
		];
		
		// Keep last 500 checks
		$log = array_slice( $log, -500 );
		
		update_option( 'wpshadow_compliance_log', $log );
	}
	
	/**
	 * Report compliance conflict
	 * 
	 * User can report problems to improve conflict database.
	 * 
	 * @param string $treatment_class Treatment class
	 * @param string $issue Issue description
	 * @param string $severity Issue severity
	 */
	public static function report_conflict( string $treatment_class, string $issue, string $severity = 'warning' ): void {
		$conflicts_db = get_option( 'wpshadow_treatment_conflicts', [] );
		
		if ( ! isset( $conflicts_db[ $treatment_class ] ) ) {
			$conflicts_db[ $treatment_class ] = [];
		}
		
		$conflicts_db[ $treatment_class ][] = [
			'description' => sanitize_text_field( $issue ),
			'severity'    => in_array( $severity, ['warning', 'critical'], true ) ? $severity : 'warning',
			'reported_at' => current_time( 'mysql' ),
			'reports'     => 1,
		];
		
		update_option( 'wpshadow_treatment_conflicts', $conflicts_db );
		
		// Track KPI
		KPI_Tracker::record_action( 'conflict_reported', 1 );
	}
	
	/**
	 * Get compliance summary
	 * 
	 * @return array Summary statistics
	 */
	public static function get_summary(): array {
		$log = get_option( 'wpshadow_compliance_log', [] );
		
		$total = count( $log );
		$compliant = count( array_filter( $log, fn( $l ) => $l['compliant'] ) );
		
		return [
			'total_checks'        => $total,
			'compliant_count'     => $compliant,
			'compliance_rate'     => $total > 0 ? round( ( $compliant / $total ) * 100, 1 ) : 100,
			'average_issues'      => $total > 0 ? round( array_sum( array_column( $log, 'issues' ) ) / $total, 1 ) : 0,
			'average_warnings'    => $total > 0 ? round( array_sum( array_column( $log, 'warnings' ) ) / $total, 1 ) : 0,
		];
	}
}
