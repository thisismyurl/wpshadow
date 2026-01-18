<?php
/**
 * Temporary script to disable all features except Asset Version Removal
 * 
 * This allows focus on perfecting the asset-version-removal feature
 * as a prototype for all other features.
 * 
 * Usage: wp eval-file disable-features-except-asset-version.php
 * 
 * @package WPShadow
 */

// Load WordPress
require_once __DIR__ . '/../../../wp-load.php';

// Get current feature toggles
$toggles = get_option( 'wpshadow_feature_toggles', array() );

echo "Current feature toggles:\n";
echo "========================\n";
foreach ( $toggles as $feature_id => $enabled ) {
	echo sprintf( "- %s: %s\n", $feature_id, $enabled ? 'enabled' : 'disabled' );
}

echo "\n\nDisabling all features except 'asset-version-removal'...\n";
echo "=======================================================\n";

// Disable all features
foreach ( $toggles as $feature_id => $enabled ) {
	if ( 'asset-version-removal' !== $feature_id ) {
		$toggles[ $feature_id ] = false;
		echo sprintf( "✓ Disabled: %s\n", $feature_id );
	} else {
		$toggles[ $feature_id ] = true;
		echo sprintf( "✓ Enabled: %s (kept active)\n", $feature_id );
	}
}

// Enable the asset-version-removal sub-features
echo "\n\nEnabling Asset Version Removal sub-features...\n";
echo "==============================================\n";

$sub_features = array(
	'wpshadow_asset-version-removal_remove_css_versions'      => true,
	'wpshadow_asset-version-removal_remove_js_versions'       => true,
	'wpshadow_asset-version-removal_preserve_plugin_versions' => false,
);

foreach ( $sub_features as $option_name => $value ) {
	update_option( $option_name, $value, false );
	echo sprintf( "✓ Set %s = %s\n", $option_name, $value ? 'true' : 'false' );
}

// Save updated toggles
update_option( 'wpshadow_feature_toggles', $toggles, false );

echo "\n\n✅ Complete! Only 'asset-version-removal' is now enabled.\n";
echo "You can now focus on perfecting this feature as a prototype.\n\n";

echo "Current state:\n";
echo "==============\n";
$final_toggles = get_option( 'wpshadow_feature_toggles', array() );
$enabled_count = 0;
$disabled_count = 0;

foreach ( $final_toggles as $feature_id => $enabled ) {
	if ( $enabled ) {
		echo sprintf( "✓ ENABLED: %s\n", $feature_id );
		$enabled_count++;
	} else {
		$disabled_count++;
	}
}

echo sprintf( "\nTotal: %d enabled, %d disabled\n", $enabled_count, $disabled_count );
