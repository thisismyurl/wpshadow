#!/bin/bash
# Emergency Fixes Verification Script
# Checks that all catastrophic fixes are in place

echo "🔍 WPShadow Emergency Fixes Verification"
echo "=========================================="
echo ""

PASS=0
FAIL=0

# Check Fix #1: Database Export Streaming
echo "🔴 Fix #1: Database Export Streaming..."
if grep -q "MURPHY-SAFE: Stream rows in chunks" includes/features/vault/class-vault-manager.php; then
    echo "   ✅ Streaming implementation found"
    ((PASS++))
else
    echo "   ❌ Streaming implementation NOT found"
    ((FAIL++))
fi

if grep -q "disk_free_space" includes/features/vault/class-vault-manager.php; then
    echo "   ✅ Disk space check found"
    ((PASS++))
else
    echo "   ❌ Disk space check NOT found"
    ((FAIL++))
fi

if grep -q "fwrite.*fp" includes/features/vault/class-vault-manager.php; then
    echo "   ✅ File handle streaming found"
    ((PASS++))
else
    echo "   ❌ File handle streaming NOT found"
    ((FAIL++))
fi

if grep -q "LIMIT.*OFFSET" includes/features/vault/class-vault-manager.php; then
    echo "   ✅ Chunked queries found"
    ((PASS++))
else
    echo "   ❌ Chunked queries NOT found"
    ((FAIL++))
fi

echo ""

# Check Fix #2: Deep Scan Time Limit
echo "🔴 Fix #2: Deep Scan Time Limit Bug..."
if grep -q "start_time = microtime" includes/features/guardian/class-scan-scheduler.php; then
    echo "   ✅ Start time tracking found"
    ((PASS++))
else
    echo "   ❌ Start time tracking NOT found"
    ((FAIL++))
fi

if grep -q "elapsed = microtime.*start_time" includes/features/guardian/class-scan-scheduler.php; then
    echo "   ✅ Elapsed time calculation found"
    ((PASS++))
else
    echo "   ❌ Elapsed time calculation NOT found"
    ((FAIL++))
fi

if grep -q "time_limit.*- 30" includes/features/guardian/class-scan-scheduler.php; then
    echo "   ✅ 30-second buffer found"
    ((PASS++))
else
    echo "   ❌ 30-second buffer NOT found"
    ((FAIL++))
fi

echo ""

# Check Fix #3: Scan Lock
echo "🟡 Fix #3: Scan Lock..."
if grep -q "get_transient.*wpshadow_scan_running" includes/admin/ajax/deep-scan-handler.php; then
    echo "   ✅ Lock check found"
    ((PASS++))
else
    echo "   ❌ Lock check NOT found"
    ((FAIL++))
fi

if grep -q "set_transient.*wpshadow_scan_running" includes/admin/ajax/deep-scan-handler.php; then
    echo "   ✅ Lock creation found"
    ((PASS++))
else
    echo "   ❌ Lock creation NOT found"
    ((FAIL++))
fi

if grep -q "delete_transient.*wpshadow_scan_running" includes/admin/ajax/deep-scan-handler.php; then
    echo "   ✅ Lock cleanup found"
    ((PASS++))
else
    echo "   ❌ Lock cleanup NOT found"
    ((FAIL++))
fi

echo ""
echo "=========================================="
echo "📊 Results: ✅ $PASS passed | ❌ $FAIL failed"
echo ""

if [ $FAIL -eq 0 ]; then
    echo "🎉 ALL FIXES VERIFIED!"
    echo ""
    echo "Next steps:"
    echo "1. Deploy to staging environment"
    echo "2. Run test suite (see EMERGENCY_FIXES_COMPLETE.md)"
    echo "3. Test large database backup (500MB+)"
    echo "4. Test deep scan with 30s timeout"
    echo "5. Test concurrent scan requests"
    echo "6. Deploy to production"
    exit 0
else
    echo "⚠️  SOME FIXES MISSING!"
    echo "Review EMERGENCY_FIXES_COMPLETE.md for details"
    exit 1
fi
