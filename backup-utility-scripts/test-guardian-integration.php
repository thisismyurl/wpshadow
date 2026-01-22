#!/usr/bin/env php
<?php
/**
 * Guardian Core System - Integration Test
 * 
 * Verifies all Guardian components load and work together
 * Run: php test-guardian-integration.php
 */

declare(strict_types=1);

// Mock WordPress functions for testing
if ( ! function_exists( 'get_option' ) ) {
	function get_option( $option, $default = false ) {
		global $mock_options;
		return $mock_options[ $option ] ?? $default;
	}
	
	function update_option( $option, $value ) {
		global $mock_options;
		$mock_options[ $option ] = $value;
		return true;
	}
	
	function delete_option( $option ) {
		global $mock_options;
		unset( $mock_options[ $option ] );
		return true;
	}
	
	function current_time( $format = 'mysql' ) {
		return gmdate( 'Y-m-d H:i:s' );
	}
	
	function wp_next_scheduled( $hook ) {
		return false;
	}
	
	function wp_schedule_event( $timestamp, $recurrence, $hook ) {
		return true;
	}
	
	function wp_clear_scheduled_hook( $hook ) {
		return true;
	}
	
	function sanitize_text_field( $str ) {
		return trim( strip_tags( $str ) );
	}
	
	function sanitize_key( $str ) {
		return strtolower( trim( $str ) );
	}
	
	function esc_html( $str ) {
		return htmlspecialchars( $str, ENT_QUOTES, 'UTF-8' );
	}
	
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

$mock_options = [];
$test_results = [];

echo "\n🧪 Guardian Core System - Integration Test\n";
echo str_repeat( '=', 50 ) . "\n\n";

// Test 1: Load Guardian_Manager
echo "Test 1: Loading Guardian_Manager... ";
try {
	require_once __DIR__ . '/includes/guardian/class-guardian-manager.php';
	$test_results['guardian_manager_load'] = true;
	echo "✅ PASS\n";
} catch ( Exception $e ) {
	$test_results['guardian_manager_load'] = false;
	echo "❌ FAIL: {$e->getMessage()}\n";
}

// Test 2: Load Guardian_Activity_Logger
echo "Test 2: Loading Guardian_Activity_Logger... ";
try {
	require_once __DIR__ . '/includes/guardian/class-guardian-activity-logger.php';
	$test_results['activity_logger_load'] = true;
	echo "✅ PASS\n";
} catch ( Exception $e ) {
	$test_results['activity_logger_load'] = false;
	echo "❌ FAIL: {$e->getMessage()}\n";
}

// Test 3: Load Baseline_Manager
echo "Test 3: Loading Baseline_Manager... ";
try {
	require_once __DIR__ . '/includes/guardian/class-baseline-manager.php';
	$test_results['baseline_manager_load'] = true;
	echo "✅ PASS\n";
} catch ( Exception $e ) {
	$test_results['baseline_manager_load'] = false;
	echo "❌ FAIL: {$e->getMessage()}\n";
}

// Test 4: Load Backup_Manager
echo "Test 4: Loading Backup_Manager... ";
try {
	require_once __DIR__ . '/includes/guardian/class-backup-manager.php';
	$test_results['backup_manager_load'] = true;
	echo "✅ PASS\n";
} catch ( Exception $e ) {
	$test_results['backup_manager_load'] = false;
	echo "❌ FAIL: {$e->getMessage()}\n";
}

// Test 5: Load Enable_Guardian_Command
echo "Test 5: Loading Enable_Guardian_Command... ";
try {
	// Load base command class first
	require_once __DIR__ . '/includes/workflow/class-command.php';
	require_once __DIR__ . '/includes/workflow/commands/class-enable-guardian-command.php';
	$test_results['enable_guardian_command_load'] = true;
	echo "✅ PASS\n";
} catch ( Exception $e ) {
	$test_results['enable_guardian_command_load'] = false;
	echo "❌ FAIL: {$e->getMessage()}\n";
}

// Test 6: Load Configure_Guardian_Command
echo "Test 6: Loading Configure_Guardian_Command... ";
try {
	require_once __DIR__ . '/includes/workflow/commands/class-configure-guardian-command.php';
	$test_results['configure_guardian_command_load'] = true;
	echo "✅ PASS\n";
} catch ( Exception $e ) {
	$test_results['configure_guardian_command_load'] = false;
	echo "❌ FAIL: {$e->getMessage()}\n";
}

// Test 7: Guardian_Manager methods exist
echo "Test 7: Guardian_Manager methods... ";
try {
	$methods = [
		'init',
		'is_enabled',
		'enable',
		'disable',
		'get_settings',
		'update_settings',
		'get_status',
	];
	foreach ( $methods as $method ) {
		if ( ! method_exists( 'WPShadow\\Guardian\\Guardian_Manager', $method ) ) {
			throw new Exception( "Missing method: {$method}" );
		}
	}
	$test_results['guardian_manager_methods'] = true;
	echo "✅ PASS\n";
} catch ( Exception $e ) {
	$test_results['guardian_manager_methods'] = false;
	echo "❌ FAIL: {$e->getMessage()}\n";
}

// Test 8: Guardian_Activity_Logger methods exist
echo "Test 8: Guardian_Activity_Logger methods... ";
try {
	$methods = [
		'log_health_check',
		'log_auto_fix',
		'get_activity_log',
		'get_statistics',
	];
	foreach ( $methods as $method ) {
		if ( ! method_exists( 'WPShadow\\Guardian\\Guardian_Activity_Logger', $method ) ) {
			throw new Exception( "Missing method: {$method}" );
		}
	}
	$test_results['activity_logger_methods'] = true;
	echo "✅ PASS\n";
} catch ( Exception $e ) {
	$test_results['activity_logger_methods'] = false;
	echo "❌ FAIL: {$e->getMessage()}\n";
}

// Test 9: Baseline_Manager methods exist
echo "Test 9: Baseline_Manager methods... ";
try {
	$methods = [
		'create_baseline',
		'detect_changes',
		'get_baseline',
	];
	foreach ( $methods as $method ) {
		if ( ! method_exists( 'WPShadow\\Guardian\\Baseline_Manager', $method ) ) {
			throw new Exception( "Missing method: {$method}" );
		}
	}
	$test_results['baseline_manager_methods'] = true;
	echo "✅ PASS\n";
} catch ( Exception $e ) {
	$test_results['baseline_manager_methods'] = false;
	echo "❌ FAIL: {$e->getMessage()}\n";
}

// Test 10: Enable_Guardian_Command methods exist
echo "Test 10: Enable_Guardian_Command methods... ";
try {
	$methods = [
		'get_name',
		'get_description',
		'get_parameters',
		'execute',
	];
	foreach ( $methods as $method ) {
		if ( ! method_exists( 'WPShadow\\Workflow\\Commands\\Enable_Guardian_Command', $method ) ) {
			throw new Exception( "Missing method: {$method}" );
		}
	}
	$test_results['enable_guardian_methods'] = true;
	echo "✅ PASS\n";
} catch ( Exception $e ) {
	$test_results['enable_guardian_methods'] = false;
	echo "❌ FAIL: {$e->getMessage()}\n";
}

// Summary
echo "\n" . str_repeat( '=', 50 ) . "\n";
$passed = array_sum( array_map( fn( $r ) => $r ? 1 : 0, $test_results ) );
$total = count( $test_results );
echo "Results: $passed/$total tests passed\n";

if ( $passed === $total ) {
	echo "✅ All tests passed!\n";
	exit( 0 );
} else {
	echo "❌ Some tests failed\n";
	exit( 1 );
}
