#!/bin/bash

# Check Phase 1 files for context arrays

DIAGNOSTICS_DIR="/workspaces/wpshadow/includes/diagnostics/tests/security"

echo "🔍 Phase 1 Files Status: Checking which need enhancement"
echo ""

# SQL Injection files
echo "📋 SQL Injection Files:"
for file in $(find "$DIAGNOSTICS_DIR" -name "*sql*injection*" -o -name "*second*order*sql*" | sort); do
    filename=$(basename "$file")
    if grep -q "'context'" "$file"; then
        echo "  ✅ $filename"
    else
        echo "  ❌ $filename"
    fi
done

echo ""
echo "📋 XSS Files:"
for file in $(find "$DIAGNOSTICS_DIR" -name "*xss*" | sort); do
    filename=$(basename "$file")
    if grep -q "'context'" "$file"; then
        echo "  ✅ $filename"
    else
        echo "  ❌ $filename"
    fi
done

echo ""
echo "📋 API Files (sample):"
for file in $(find "$DIAGNOSTICS_DIR" -name "*api*" | head -8 | sort); do
    filename=$(basename "$file")
    if grep -q "'context'" "$file"; then
        echo "  ✅ $filename"
    else
        echo "  ❌ $filename"
    fi
done

echo ""
echo "📋 Login/Authentication Files (sample):"
for file in $(find "$DIAGNOSTICS_DIR" \( -name "*login*" -o -name "*authentication*" -o -name "*brute*" \) | head -8 | sort); do
    filename=$(basename "$file")
    if grep -q "'context'" "$file"; then
        echo "  ✅ $filename"
    else
        echo "  ❌ $filename"
    fi
done
