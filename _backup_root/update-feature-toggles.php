#!/usr/bin/env php
<?php
/**
 * Standalone script to disable all features except Asset Version Removal
 * 
 * This script directly connects to the database and updates the feature toggles.
 * Can be run without WordPress loaded.
 * 
 * Usage: php update-feature-toggles.php
 * 
 * @package WPShadow
 */

// Database connection settings from docker-compose.yml
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'wordpress';
$db_user = getenv('DB_USER') ?: 'wordpress';
$db_pass = getenv('DB_PASS') ?: 'wordpress';
$db_port = getenv('DB_PORT') ?: '3306';
$table_prefix = getenv('TABLE_PREFIX') ?: 'wp_';

echo "WPShadow Feature Toggle Manager\n";
echo "================================\n\n";

// Try to connect to database
try {
	$dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
	$pdo = new PDO($dsn, $db_user, $db_pass, [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]);
	
	echo "✓ Connected to database\n\n";
	
	// Get current feature toggles
	$stmt = $pdo->prepare("SELECT option_value FROM {$table_prefix}options WHERE option_name = 'wpshadow_feature_toggles'");
	$stmt->execute();
	$result = $stmt->fetch();
	
	if (!$result) {
		echo "❌ No feature toggles found in database. Plugin may not be activated.\n";
		exit(1);
	}
	
	$toggles = unserialize($result['option_value']);
	
	if (!is_array($toggles)) {
		echo "❌ Feature toggles data is corrupted.\n";
		exit(1);
	}
	
	echo "Current features (" . count($toggles) . " total):\n";
	echo str_repeat("-", 50) . "\n";
	
	$enabled = 0;
	$disabled = 0;
	
	foreach ($toggles as $feature_id => $is_enabled) {
		if ($is_enabled) {
			echo sprintf("  ✓ %s (enabled)\n", $feature_id);
			$enabled++;
		} else {
			echo sprintf("  ○ %s (disabled)\n", $feature_id);
			$disabled++;
		}
	}
	
	echo sprintf("\nSummary: %d enabled, %d disabled\n\n", $enabled, $disabled);
	
	// Disable all except asset-version-removal
	echo "Updating feature toggles...\n";
	echo str_repeat("-", 50) . "\n";
	
	foreach ($toggles as $feature_id => $is_enabled) {
		if ($feature_id === 'asset-version-removal') {
			$toggles[$feature_id] = true;
			echo sprintf("  ✓ KEEPING ENABLED: %s\n", $feature_id);
		} else {
			$toggles[$feature_id] = false;
			echo sprintf("  ○ DISABLING: %s\n", $feature_id);
		}
	}
	
	// Serialize and update
	$serialized = serialize($toggles);
	$stmt = $pdo->prepare("UPDATE {$table_prefix}options SET option_value = ? WHERE option_name = 'wpshadow_feature_toggles'");
	$stmt->execute([$serialized]);
	
	echo "\n✅ Feature toggles updated successfully!\n\n";
	
	// Update sub-features
	echo "Setting Asset Version Removal sub-features...\n";
	echo str_repeat("-", 50) . "\n";
	
	$sub_features = [
		'wpshadow_asset-version-removal_remove_css_versions' => true,
		'wpshadow_asset-version-removal_remove_js_versions' => true,
		'wpshadow_asset-version-removal_preserve_plugin_versions' => false,
	];
	
	foreach ($sub_features as $option_name => $value) {
		$serialized_value = serialize($value);
		
		// Check if option exists
		$stmt = $pdo->prepare("SELECT option_id FROM {$table_prefix}options WHERE option_name = ?");
		$stmt->execute([$option_name]);
		$exists = $stmt->fetch();
		
		if ($exists) {
			// Update existing
			$stmt = $pdo->prepare("UPDATE {$table_prefix}options SET option_value = ? WHERE option_name = ?");
			$stmt->execute([$serialized_value, $option_name]);
			echo sprintf("  ✓ Updated: %s = %s\n", $option_name, $value ? 'true' : 'false');
		} else {
			// Insert new
			$stmt = $pdo->prepare("INSERT INTO {$table_prefix}options (option_name, option_value, autoload) VALUES (?, ?, 'no')");
			$stmt->execute([$option_name, $serialized_value]);
			echo sprintf("  ✓ Created: %s = %s\n", $option_name, $value ? 'true' : 'false');
		}
	}
	
	echo "\n✅ All done!\n\n";
	echo "Final State:\n";
	echo str_repeat("=", 50) . "\n";
	echo "  ✓ ENABLED:  asset-version-removal\n";
	echo "  ✓ Sub-feature: remove_css_versions = ON\n";
	echo "  ✓ Sub-feature: remove_js_versions = ON\n";
	echo "  ○ Sub-feature: preserve_plugin_versions = OFF\n";
	echo "\n  ○ All other features: DISABLED\n\n";
	
	echo "You can now focus on perfecting the Asset Version Removal\n";
	echo "feature as a prototype for all other features.\n";
	
} catch (PDOException $e) {
	echo "❌ Database connection failed: " . $e->getMessage() . "\n";
	echo "\nTry running with environment variables:\n";
	echo "  DB_HOST=localhost DB_NAME=wordpress DB_USER=wordpress DB_PASS=wordpress php update-feature-toggles.php\n\n";
	exit(1);
}
