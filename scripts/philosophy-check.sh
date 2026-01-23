#!/bin/bash
# Philosophy Compliance Checker
# Validates commits align with WPShadow 11 Commandments
# Runs as part of pre-commit hook

set -e

# Colors
RED='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}→ Checking philosophy compliance...${NC}"

PHILOSOPHY_ISSUES=0
CRITICAL_ISSUES=0

# === 1. Check for paywalls in diagnostics/treatments ===
echo "  Checking for philosophy violations (paywalls, dark patterns)..."
for file in $(git diff --cached --name-only --diff-filter=ACM | grep -E '(diagnostics|treatments)/class-' | grep '\.php$'); do
    if [ -f "$file" ]; then
        # Look for suspicious paywall patterns
        if grep -n "is_pro\|upgrade_required\|premium_only\|contact_sales" "$file" 2>/dev/null | grep -v "comment\|//" > /dev/null 2>&1; then
            echo -e "${YELLOW}⚠️  Possible paywall pattern in: $file${NC}"
            grep -n "is_pro\|upgrade_required" "$file" || true
            echo -e "${YELLOW}    Reminder: Commandment #2 - Free as Possible${NC}"
            ((PHILOSOPHY_ISSUES++))
        fi
    fi
done

# === 2. Check for missing KB/training links in new diagnostics ===
echo "  Checking for KB/training links in diagnostics..."
for file in $(git diff --cached --name-only --diff-filter=ACM | grep 'diagnostics/class-' | grep '\.php$'); do
    if [ -f "$file" ]; then
        # Check if it has get_description() with KB link
        if grep -q "class Diagnostic" "$file"; then
            if ! grep -q "wpshadow.com/kb\|training\|learn more" "$file" 2>/dev/null; then
                echo -e "${YELLOW}⚠️  No KB/training links in: $file${NC}"
                echo -e "${YELLOW}    Reminder: Commandment #5-6 - Drive to KB & Training${NC}"
                ((PHILOSOPHY_ISSUES++))
            fi
        fi
    fi
done

# === 2b. Enforce strict types declaration ===
echo "  Checking for strict types..."
for file in $(git diff --cached --name-only --diff-filter=ACM | grep -E '\\.php$' | grep -E '^(includes/|wpshadow\\.php)'); do
    if [ -f "$file" ]; then
        if ! head -5 "$file" | grep -q "declare(strict_types=1);"; then
            echo -e "${YELLOW}⚠️  Missing declare(strict_types=1): $file${NC}"
            ((PHILOSOPHY_ISSUES++))
        fi
    fi
done

# === 3. Check for reversible treatments ===
echo "  Checking for treatment reversibility..."
for file in $(git diff --cached --name-only --diff-filter=ACM | grep 'treatments/class-' | grep '\.php$'); do
    if [ -f "$file" ]; then
        if grep -q "class Treatment" "$file"; then
            if ! grep -q "public static function undo()" "$file" 2>/dev/null; then
                echo -e "${RED}❌ Treatment missing undo() method: $file${NC}"
                echo -e "${RED}    Commandment #2 requires reversibility${NC}"
                ((PHILOSOPHY_ISSUES++))
            fi
        fi
    fi
done

# === 4. Check for KPI tracking in treatments ===
echo "  Checking for KPI tracking..."
for file in $(git diff --cached --name-only --diff-filter=ACM | grep 'treatments/class-' | grep '\.php$'); do
    if [ -f "$file" ]; then
        if grep -q "public static function apply()" "$file"; then
            if ! grep -q "KPI_Tracker\|record_treatment" "$file" 2>/dev/null; then
                echo -e "${YELLOW}⚠️  No KPI tracking in: $file${NC}"
                echo -e "${YELLOW}    Reminder: Commandment #9 - Show Value${NC}"
                ((PHILOSOPHY_ISSUES++))
            fi
        fi
    fi
done

# === 4b. Check for dangerous patterns (eval, raw SQL) ===
echo "  Checking for dangerous patterns..."
for file in $(git diff --cached --name-only --diff-filter=ACM | grep -E '\\.php$' | grep -v 'vendor/'); do
    if [ -f "$file" ]; then
        if grep -q "eval\\(" "$file" 2>/dev/null; then
            echo -e "${RED}❌ eval() found in: $file${NC}"
            echo -e "${RED}    Never use eval() (security risk)${NC}"
            ((CRITICAL_ISSUES++))
        fi
        # Raw SQL without prepare (basic heuristic)
        if grep -q "\\$wpdb->query\\(" "$file" 2>/dev/null; then
            if ! grep -q "\\$wpdb->prepare" "$file" 2>/dev/null; then
                echo -e "${YELLOW}⚠️  Potential raw SQL without prepare: $file${NC}"
                ((PHILOSOPHY_ISSUES++))
            fi
        fi
    fi
done

# === 5. Check commit message for philosophy alignment ===
echo "  Checking commit message..."
COMMIT_MSG=$(git diff --cached --diff-filter=ACM --quiet 2>/dev/null; git log -1 --pretty=%B 2>/dev/null || echo "")

# Flag if commit removes or disables features without explanation
if echo "$COMMIT_MSG" | grep -qi "remove\|disable\|delete"; then
    if ! echo "$COMMIT_MSG" | grep -qi "why\|reason\|because\|benefit"; then
        echo -e "${YELLOW}⚠️  Removal detected without explanation${NC}"
        echo -e "${YELLOW}    Consider explaining the philosophy reason${NC}"
    fi
fi

# === Summary ===
if [ "$PHILOSOPHY_ISSUES" -gt 0 ]; then
    echo ""
    echo -e "${YELLOW}⚠️  Found $PHILOSOPHY_ISSUES philosophy-related items to review${NC}"
    echo -e "${YELLOW}Not blocking (warnings only), but consider the above.${NC}"
elif [ "$CRITICAL_ISSUES" -gt 0 ]; then
    echo ""
    echo -e "${RED}❌ Found $CRITICAL_ISSUES critical security issues${NC}"
    exit 1
else
    echo -e "${GREEN}✅ Philosophy compliance check passed${NC}"
fi
