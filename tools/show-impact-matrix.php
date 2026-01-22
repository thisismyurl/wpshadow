#!/usr/bin/php
<?php
/**
 * Performance Impact Matrix Display Tool
 * 
 * Shows all diagnostics organized by impact level and Guardian suitability
 * Usage: php tools/show-impact-matrix.php
 */

// Simulate the classifier for standalone use
require_once __DIR__ . '/../includes/core/class-performance-impact-classifier.php';

use WPShadow\Core\Performance_Impact_Classifier;

function display_matrix() {
	echo "\n";
	echo "╔═══════════════════════════════════════════════════════════════════════════╗\n";
	echo "║          WPShadow Diagnostic Performance Impact Matrix                    ║\n";
	echo "║                   (Guardian Integration Ready)                            ║\n";
	echo "╚═══════════════════════════════════════════════════════════════════════════╝\n";
	echo "\n";

	// Get statistics
	$stats = Performance_Impact_Classifier::get_stats();

	echo "SUMMARY STATISTICS\n";
	echo "─────────────────────────────────────────────────────────────────────────────\n";
	printf("Total Diagnostics:    %d\n", $stats['total']);
	printf("Average Impact:       %.1f ms\n", $stats['avg_ms']);
	printf("Total Combined Time:  %.1f s (if all sequential)\n\n", $stats['total_ms'] / 1000);

	// By Impact Level
	echo "DISTRIBUTION BY IMPACT LEVEL\n";
	echo "─────────────────────────────────────────────────────────────────────────────\n";
	$impact_levels = [
		'minimal'    => '✓ MINIMAL (5-25ms)',
		'low'        => '✓ LOW (25-100ms)',
		'medium'     => '⚠ MEDIUM (100-500ms)',
		'high'       => '⚠ HIGH (500ms-2s)',
		'very_high'  => '⚠ VERY HIGH (2-5s)',
		'extreme'    => '🔴 EXTREME (5s+)',
	];

	foreach ( $impact_levels as $key => $label ) {
		$count = $stats['by_impact'][ $key ] ?? 0;
		printf("%-35s %3d diagnostics\n", $label, $count);
	}
	echo "\n";

	// By Guardian Suitability
	echo "DISTRIBUTION BY GUARDIAN SUITABILITY\n";
	echo "─────────────────────────────────────────────────────────────────────────────\n";
	$guardian_levels = [
		'anytime'    => '✓ ANYTIME (Run any request)',
		'background' => '⚡ BACKGROUND (Job queue)',
		'scheduled'  => '⏰ SCHEDULED (Off-peak)',
		'manual'     => '🔒 MANUAL (User triggered)',
	];

	foreach ( $guardian_levels as $key => $label ) {
		$count = $stats['by_guardian'][ $key ] ?? 0;
		printf("%-35s %3d diagnostics\n", $label, $count);
	}
	echo "\n";

	// Show detailed breakdown by impact
	echo "DETAILED BREAKDOWN BY IMPACT LEVEL\n";
	echo "═════════════════════════════════════════════════════════════════════════════\n";

	$by_impact = [
		'minimal'    => '✓ MINIMAL IMPACT (Anytime Safe)',
		'low'        => '✓ LOW IMPACT (Anytime Safe)',
		'medium'     => '⚠ MEDIUM IMPACT (Batch Acceptable)',
		'high'       => '⚠ HIGH IMPACT (Off-Peak Preferred)',
		'very_high'  => '⚠ VERY HIGH IMPACT (Off-Peak Strongly)',
		'extreme'    => '🔴 EXTREME IMPACT (Manual Only)',
	];

	foreach ( $by_impact as $level => $header ) {
		$diagnostics = Performance_Impact_Classifier::get_by_impact( $level );
		if ( empty( $diagnostics ) ) {
			continue;
		}

		echo "\n" . str_repeat( "─", 77 ) . "\n";
		echo $header . "\n";
		echo str_repeat( "─", 77 ) . "\n";

		foreach ( $diagnostics as $slug => $config ) {
			$time = Performance_Impact_Classifier::calculate_time( $config['factors'] ?? [] );
			$guardian_label = str_pad(
				ucfirst( str_replace( '_', ' ', $config['guardian'] ) ),
				12
			);
			printf(
				"  %-40s %8.0f ms   %s\n",
				$slug,
				$time,
				$guardian_label
			);
		}
	}

	echo "\n";
	echo str_repeat( "═", 77 ) . "\n";
	echo "\n";

	// Show Guardian execution strategy
	echo "GUARDIAN EXECUTION STRATEGY\n";
	echo "═════════════════════════════════════════════════════════════════════════════\n";
	echo "\n";

	echo "1. ANYTIME EXECUTION (During Every Heartbeat)\n";
	echo "   ──────────────────────────────────────────\n";
	$anytime = Performance_Impact_Classifier::get_guardian_suitable( 'anytime' );
	$anytime_time = array_sum( array_map(
		fn( $c ) => Performance_Impact_Classifier::calculate_time( $c['factors'] ?? [] ),
		$anytime
	) );
	printf(
		"   • %d diagnostics | Total: %.0f ms | Suitable for every request\n\n",
		count( $anytime ),
		$anytime_time
	);

	echo "2. BACKGROUND JOBS (Job Queue)\n";
	echo "   ─────────────────────────────\n";
	$background = Performance_Impact_Classifier::get_guardian_suitable( 'background' );
	$bg_time = array_sum( array_map(
		fn( $c ) => Performance_Impact_Classifier::calculate_time( $c['factors'] ?? [] ),
		$background
	) );
	printf(
		"   • %d diagnostics | Total: %.0f ms (%.1f sec) | Run 5-10 per batch\n",
		count( $background ),
		$bg_time,
		$bg_time / 1000
	);
	printf(
		"   • Estimated full cycle: %.1f hours\n\n",
		( $bg_time / 1000 ) / 60
	);

	echo "3. SCHEDULED JOBS (Off-Peak Only)\n";
	echo "   ───────────────────────────────\n";
	$scheduled = Performance_Impact_Classifier::get_guardian_suitable( 'scheduled' );
	$sched_time = array_sum( array_map(
		fn( $c ) => Performance_Impact_Classifier::calculate_time( $c['factors'] ?? [] ),
		$scheduled
	) );
	printf(
		"   • %d diagnostics | Total: %.0f ms (%.1f sec) | Run during 2-6 AM\n",
		count( $scheduled ),
		$sched_time,
		$sched_time / 1000
	);
	printf(
		"   • Recommendation: Spread across multiple nights\n\n"
	);

	echo "4. MANUAL ONLY (User-Triggered)\n";
	echo "   ─────────────────────────────\n";
	$manual = array_filter(
		Performance_Impact_Classifier::get_off_peak_suitable(),
		fn( $c ) => $c['guardian'] === 'manual'
	);
	$manual_time = array_sum( array_map(
		fn( $c ) => Performance_Impact_Classifier::calculate_time( $c['factors'] ?? [] ),
		$manual
	) );
	printf(
		"   • %d diagnostics | Up to %.0f ms (%.1f sec) per run\n",
		count( $manual ),
		$manual_time,
		$manual_time / 1000
	);
	printf(
		"   • Requires user confirmation\n\n"
	);

	echo str_repeat( "═", 77 ) . "\n";
	echo "\n";

	// Sample execution scenarios
	echo "SAMPLE EXECUTION SCENARIOS\n";
	echo "═════════════════════════════════════════════════════════════════════════════\n";
	echo "\n";

	echo "Scenario 1: User Request During Peak Hours\n";
	echo "───────────────────────────────────────────\n";
	echo "  Execution Mode: ANYTIME ONLY\n";
	echo "  Maximum Impact: ~" . round( $anytime_time ) . " ms\n";
	echo "  Suitable: YES (negligible user impact)\n\n";

	echo "Scenario 2: Nightly Background Job (3 AM)\n";
	echo "──────────────────────────────────────────\n";
	printf(
		"  Execution Mode: ANYTIME + BACKGROUND + SCHEDULED\n"
	);
	printf(
		"  Total Impact: ~%.0f ms (%.1f seconds)\n",
		$anytime_time + $bg_time,
		( $anytime_time + $bg_time ) / 1000
	);
	echo "  Server Load: Moderate\n\n";

	echo "Scenario 3: Weekly Full System Scan (Sunday 2 AM)\n";
	echo "──────────────────────────────────────────────────\n";
	$all_time = $anytime_time + $bg_time + $sched_time;
	printf(
		"  Execution Mode: ALL EXCEPT MANUAL\n"
	);
	printf(
		"  Total Impact: ~%.0f ms (%.1f seconds or %.1f minutes)\n",
		$all_time,
		$all_time / 1000,
		$all_time / 60000
	);
	echo "  Server Load: High (but acceptable off-peak)\n\n";

	echo str_repeat( "═", 77 ) . "\n";
	echo "\n";

	// Key recommendations
	echo "KEY RECOMMENDATIONS\n";
	echo "═════════════════════════════════════════════════════════════════════════════\n";
	echo "\n";
	echo "✓ Only run " . count( $anytime ) . " low-impact diagnostics during user requests\n";
	echo "✓ Batch " . count( $background ) . " medium-impact diagnostics (5-10 per batch)\n";
	echo "✓ Schedule " . count( $scheduled ) . " high-impact diagnostics for 2-6 AM only\n";
	echo "✓ Manual diagnostics require user confirmation before execution\n";
	echo "✓ Guardian should follow these rules automatically\n";
	echo "✓ Broken link checks should be monthly or user-triggered\n";
	echo "✓ Backup operations should use Guardian cloud only\n";
	echo "\n";
}

display_matrix();
?>
