#!/bin/bash
# Check if #3680-3689 diagnostics exist

echo "Checking diagnostics #3680-3689:"
echo ""

# #3689: Yoast SEO Essential Settings
echo -n "#3689 (Yoast Essential): "
if grep -r "yoast.*essential\|sitemap.*yoast\|yoast.*sitemap" includes/diagnostics/tests --include="*.php" | grep -i "slug\|class.*Diagnostic" | head -1 > /dev/null; then
    echo "✅ EXISTS"
else
    echo "❌ NEEDS IMPLEMENTATION"
fi

# #3688: Advertising Labeling
echo -n "#3688 (Ad Labeling): "
if find includes/diagnostics/tests -name "*ad*label*.php" -o -name "*advertising*.php" | head -1 | grep -q "."; then
    echo "✅ EXISTS"
else
    echo "❌ NEEDS IMPLEMENTATION"
fi

# #3687: Paywall Transparency
echo -n "#3687 (Paywall): "
if find includes/diagnostics/tests -name "*paywall*.php" | head -1 | grep -q "."; then
    echo "✅ EXISTS"
else
    echo "❌ NEEDS IMPLEMENTATION"
fi

# #3686: News Corrections
echo -n "#3686 (Corrections): "
if find includes/diagnostics/tests -name "*correction*.php" | head -1 | grep -q "."; then
    echo "✅ EXISTS"
else
    echo "❌ NEEDS IMPLEMENTATION"
fi

# #3685: Source Protection
echo -n "#3685 (Whistleblower): "
if find includes/diagnostics/tests -name "*whistleblow*.php" -o -name "*source*protect*.php" | head -1 | grep -q "."; then
    echo "✅ EXISTS"
else
    echo "❌ NEEDS IMPLEMENTATION"
fi

echo ""
echo "=== Booking/Appointment (#3680-3684) ==="

# #3684: Team Booking
echo -n "#3684 (Team Booking): "
if grep -r "team.*booking\|multi.*user.*booking" includes/diagnostics/tests --include="*.php" | grep -i "slug\|class.*Diagnostic" | head -1 > /dev/null; then
    echo "✅ EXISTS"
else
    echo "❌ NEEDS IMPLEMENTATION"
fi

# #3683: Cancellation Policy
echo -n "#3683 (Cancellation): "
if find includes/diagnostics/tests -name "*cancel*.php" | grep -q "."; then
    echo "✅ EXISTS"
else
    echo "❌ NEEDS IMPLEMENTATION"
fi

# #3682: Calendar Integration
echo -n "#3682 (Calendar Sync): "
if find includes/diagnostics/tests -name "*calendar*sync*.php" -o -name "*calendar*integrat*.php" | head -1 | grep -q "."; then
    echo "✅ EXISTS"
else
    echo "❌ NEEDS IMPLEMENTATION"
fi

# #3681: Reminder Compliance
echo -n "#3681 (Reminders): "
if find includes/diagnostics/tests -name "*reminder*.php" | head -1 | grep -q "."; then
    echo "✅ EXISTS"
else
    echo "❌ NEEDS IMPLEMENTATION"
fi

# #3680: Booking Privacy
echo -n "#3680 (Booking Privacy): "
if grep -r "booking.*privacy\|appointment.*privacy" includes/diagnostics/tests --include="*.php" | grep -i "slug\|class.*Diagnostic" | head -1 > /dev/null; then
    echo "✅ EXISTS"
else
    echo "❌ NEEDS IMPLEMENTATION"
fi
