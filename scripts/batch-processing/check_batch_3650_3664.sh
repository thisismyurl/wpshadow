#!/bin/bash
echo "Checking #3650-3664 (E-commerce/Membership/Forum)..."
echo ""

echo -n "#3664 (Portfolio Image Optimization - DUP of #3677): DUPLICATE - "
echo "Already processed #3677"

echo -n "#3663 (E-commerce ADA Compliance): "
grep -r "ecommerce.*ada\|ecommerce.*accessibility\|woocommerce.*accessibility" includes/diagnostics/tests --include="*.php" -l | head -1 | grep -q "." && echo "✅" || echo "❌"

echo -n "#3662 (Member Privacy from Members): "
grep -r "member.*privacy.*member\|user.*privacy.*user" includes/diagnostics/tests --include="*.php" -l | head -1 | grep -q "." && echo "✅" || echo "❌"

echo -n "#3661 (Image Copyright - DUP of #3676): DUPLICATE - "
echo "Already processed #3676"

echo -n "#3660 (Marketplace Seller Verification): "
grep -r "marketplace.*seller\|vendor.*verification\|multi.*vendor" includes/diagnostics/tests --include="*.php" -l | head -1 | grep -q "." && echo "✅" || echo "❌"

echo -n "#3659 (Member Payment Security): "
grep -r "member.*payment.*security\|membership.*pci" includes/diagnostics/tests --include="*.php" -l | head -1 | grep -q "." && echo "✅" || echo "❌"

echo -n "#3658 (Forum Email Notifications - DUP of #3675): DUPLICATE - "
echo "Already closed #3675"

echo -n "#3657 (Digital Product Delivery): "
grep -r "digital.*product.*delivery\|digital.*download.*privacy" includes/diagnostics/tests --include="*.php" -l | head -1 | grep -q "." && echo "✅" || echo "❌"

echo -n "#3656 (Membership Tier Pricing): "
grep -r "membership.*tier.*pricing\|membership.*pricing.*transparency" includes/diagnostics/tests --include="*.php" -l | head -1 | grep -q "." && echo "✅" || echo "❌"

echo -n "#3655 (Forum Performance - DUP of #3674): DUPLICATE - "
echo "Already noted #3674 needs implementation"

echo -n "#3654 (Customer Data Portability): "
grep -r "customer.*data.*portability\|woocommerce.*export" includes/diagnostics/tests --include="*.php" -l | head -1 | grep -q "." && echo "✅" || echo "❌"

echo -n "#3653 (Member Content Moderation): "
grep -r "member.*content.*moderation\|user.*content.*removal" includes/diagnostics/tests --include="*.php" -l | head -1 | grep -q "." && echo "✅" || echo "❌"

echo -n "#3652 (Forum Moderation - DUP of #3673): DUPLICATE - "
echo "Already noted #3673 needs implementation"

echo -n "#3651 (Subscription E-commerce): "
grep -r "subscription.*ecommerce\|woocommerce.*subscription" includes/diagnostics/tests --include="*.php" -l | head -1 | grep -q "." && echo "✅" || echo "❌"

echo -n "#3650 (UGC Copyright - DUP of #3672): DUPLICATE - "
echo "Already noted #3672 needs implementation"
