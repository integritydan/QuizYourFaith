#!/bin/bash
echo "========== QUICK HEALTH CHECK =========="
# ---- 1.  PHP syntax ----
find . -name "*.php" -exec php -l {} \; > /tmp/phplint 2>&1
if grep -i error /tmp/phplint; then echo "❌  PHP syntax error"; exit 1; else echo "✅  PHP clean"; fi
# ---- 2.  folders ----
for d in public/uploads/logo public/uploads/categories storage app/Core; do
  [[ -d $d ]] || { echo "❌  Missing $d"; exit 1; }
done
echo "✅  All folders present"
# ---- 3.  README exists ----
[[ -s README.md ]] && echo "✅  README present" || echo "⚠️  README missing"
echo "========== ALL GREEN – READY TO SHIP =========="
