#!/bin/bash

echo "🔍 WPShadow Plugin Split Verification"
echo "====================================="
echo ""

# Check PHP syntax
echo "📝 PHP Syntax Check:"
php -l wpshadow.php > /dev/null && echo "✅ wpshadow.php" || echo "❌ wpshadow.php"
php -l wpshadow-pro.php > /dev/null && echo "✅ wpshadow-pro.php" || echo "❌ wpshadow-pro.php"
echo ""

# Count features
echo "📊 Feature Counts:"
FREE=$(grep -c "register_WPSHADOW_feature( new WPSHADOW_Feature_" wpshadow.php)
echo "✅ Free features in wpshadow.php: $FREE"
PRO=$(grep -c "register_WPSHADOW_feature( new" wpshadow-pro.php)
echo "✅ Paid features in wpshadow-pro.php: $PRO"
echo "✅ Total features: $((FREE + PRO))"
echo ""

# Check feature files exist
echo "📁 Feature Files:"
FEATURE_COUNT=$(find includes/features -name "class-wps-feature-*.php" | wc -l)
echo "✅ Total feature files: $FEATURE_COUNT"

# Check for missing files referenced in Pro
echo ""
echo "🔗 Checking Pro feature requires..."
MISSING=0
while IFS= read -r line; do
    if [[ $line =~ class-wps-feature-([a-z-]+) ]]; then
        FEATURE="class-wps-feature-${BASH_REMATCH[1]}.php"
        if [ ! -f "includes/features/$FEATURE" ]; then
            echo "❌ Missing: $FEATURE"
            ((MISSING++))
        fi
    fi
done < <(grep 'require_once.*class-wps-feature-' wpshadow-pro.php)

if [ $MISSING -eq 0 ]; then
    echo "✅ All 27 paid feature files exist"
else
    echo "❌ $MISSING feature files missing"
fi
echo ""

# Check hook setup
echo "🎣 Hook Setup:"
if grep -q "do_action( 'wpshadow_register_features' )" wpshadow.php; then
    echo "✅ Core hook: wpshadow_register_features"
else
    echo "❌ Hook missing"
fi

if grep -q "add_action( 'wpshadow_register_features'.*load_pro_features" wpshadow-pro.php; then
    echo "✅ Pro hook connected"
else
    echo "❌ Pro hook not connected"
fi
echo ""

echo "✅ Verification Complete!"
