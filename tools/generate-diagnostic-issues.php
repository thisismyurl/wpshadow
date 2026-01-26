#!/usr/bin/env php
<?php
/**
 * Generate GitHub Issues for Diagnostic Implementation
 *
 * This script reads the FEATURE_MATRIX_DIAGNOSTICS.md file and generates
 * GitHub issue content for each diagnostic that needs implementation.
 *
 * Usage:
 *   php tools/generate-diagnostic-issues.php [--diagnostic=slug] [--category=name] [--output=dir]
 *
 * Options:
 *   --diagnostic=slug  Generate issue for specific diagnostic slug
 *   --category=name    Generate issues for specific category only
 *   --output=dir       Output directory for generated issues (default: issues/)
 *   --format=type      Output format: markdown (default) or json
 *
 * @package WPShadow
 */

// Diagnostic data from FEATURE_MATRIX_DIAGNOSTICS.md
$diagnostics = array(
	// Security Diagnostics (12)
	'admin-email'            => array(
		'name'         => 'Admin Email Check',
		'purpose'      => 'Checks if admin email is public/weak',