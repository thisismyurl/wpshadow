#!/bin/bash
echo "Checking #3665-3674..."
echo ""

# Forum diagnostics
echo -n "#3674 (Forum Performance): "
grep -r "forum.*performance\|forum.*scale" includes/diagnostics/tests --include="*.php" -l | head -1 | grep -q "." && echo "✅" || echo "❌"

echo -n "#3673 (Forum Moderation): "
grep -r "forum.*moderation\|harassment.*prevention" includes/diagnostics/tests --include="*.php" -l | head -1 | grep -q "." && echo "✅" || echo "❌"

echo -n "#3672 (UGC Copyright/DMCA): "
grep -r "user.*generated.*copyright\|dmca.*takedown" includes/diagnostics/tests --include="*.php" -l | head -1 | grep -q "." && echo "✅" || echo "❌"

echo -n "#3671 (Forum Privacy): "
grep -r "forum.*privacy\|member.*data.*protection" includes/diagnostics/tests --include="*.php" -l | head -1 | grep -q "." && echo "✅" || echo "❌"

# Multisite diagnostics
echo -n "#3670 (Multisite Registration): "
grep -r "multisite.*registration\|network.*spam" includes/diagnostics/tests --include="*.php" -l | head -1 | grep -q "." && echo "✅" || echo "❌"

echo -n "#3669 (Network Plugin Security): "
grep -r "network.*plugin.*security\|network.*theme.*security" includes/diagnostics/tests --include="*.php" -l | head -1 | grep -q "." && echo "✅" || echo "❌"

echo -n "#3668 (Sub-site Data Isolation): "
grep -r "sub.*site.*isolation\|site.*data.*isolation" includes/diagnostics/tests --include="*.php" -l | head -1 | grep -q "." && echo "✅" || echo "❌"

# Duplicated - checking if already closed
echo -n "#3667 (Portfolio Accessibility - DUP): "
echo "SKIP (duplicate of #3678)"

# Membership diagnostics
echo -n "#3666 (Member Data Portability): "
grep -r "member.*portability\|data.*export.*member" includes/diagnostics/tests --include="*.php" -l | head -1 | grep -q "." && echo "✅" || echo "❌"

echo -n "#3665 (Auto-Renewal Compliance): "
grep -r "auto.*renewal\|subscription.*renewal" includes/diagnostics/tests --include="*.php" -l | head -1 | grep -q "." && echo "✅" || echo "❌"
