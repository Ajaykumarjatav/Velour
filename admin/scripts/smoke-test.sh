#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# Velour Smoke Test — AUDIT FIX: Production Deployment Readiness
#
# Run after every deployment to verify the application is functional.
# Exits 0 on success, 1 on any failure.
#
# Usage:
#   ./scripts/smoke-test.sh https://app.velour.app
#   ./scripts/smoke-test.sh (defaults to APP_URL from .env)
# ─────────────────────────────────────────────────────────────────────────────

set -euo pipefail

BASE_URL="${1:-${APP_URL:-http://localhost}}"
PASS=0
FAIL=0
ERRORS=()

green='\033[0;32m'; red='\033[0;31m'; yellow='\033[1;33m'; reset='\033[0m'

check() {
    local name="$1"
    local cmd="$2"
    local expect="${3:-200}"

    actual=$(eval "$cmd" 2>/dev/null || echo "0")
    if [ "$actual" = "$expect" ]; then
        echo -e "  ${green}✅${reset}  ${name}"
        ((PASS++))
    else
        echo -e "  ${red}❌${reset}  ${name} (expected ${expect}, got ${actual})"
        ERRORS+=("$name")
        ((FAIL++))
    fi
}

echo ""
echo -e "${yellow}🔍 Velour Smoke Tests — ${BASE_URL}${reset}"
echo "─────────────────────────────────────────────────"

# ── Health & Infrastructure ────────────────────────────────────────────────
check "Health endpoint returns 200" \
    "curl -s -o /dev/null -w '%{http_code}' '${BASE_URL}/api/v1/health'"

check "Health status is healthy/degraded" \
    "curl -s '${BASE_URL}/api/v1/health' | python3 -c \"import sys,json; d=json.load(sys.stdin); print('200' if d.get('status') in ['healthy','degraded'] else '0')\""

# ── Security Headers ───────────────────────────────────────────────────────
check "X-Content-Type-Options: nosniff present" \
    "curl -sI '${BASE_URL}/api/v1/health' | grep -c 'x-content-type-options: nosniff'"  "1"

check "X-Frame-Options: DENY present" \
    "curl -sI '${BASE_URL}/api/v1/health' | grep -ci 'x-frame-options: deny'"  "1"

check "X-Request-ID header present" \
    "curl -sI '${BASE_URL}/api/v1/health' | grep -ci 'x-request-id'"  "1"

check "X-Powered-By header absent" \
    "curl -sI '${BASE_URL}/api/v1/health' | grep -ci 'x-powered-by' || echo '0'"  "0"

# ── Web Routes ────────────────────────────────────────────────────────────
check "Login page accessible" \
    "curl -s -o /dev/null -w '%{http_code}' '${BASE_URL}/login'"

check "Register page accessible" \
    "curl -s -o /dev/null -w '%{http_code}' '${BASE_URL}/register'"

check "Privacy policy accessible" \
    "curl -s -o /dev/null -w '%{http_code}' '${BASE_URL}/legal/privacy'"

check "Terms accessible" \
    "curl -s -o /dev/null -w '%{http_code}' '${BASE_URL}/legal/terms'"

check "Help centre accessible" \
    "curl -s -o /dev/null -w '%{http_code}' '${BASE_URL}/help'"

# ── API Routes ────────────────────────────────────────────────────────────
check "API unauthenticated returns 401" \
    "curl -s -o /dev/null -w '%{http_code}' '${BASE_URL}/api/v1/auth/me'"  "401"

check "API unknown route returns 404 JSON" \
    "curl -s -o /dev/null -w '%{http_code}' -H 'Accept: application/json' '${BASE_URL}/api/v1/nonexistent'"  "404"

# ── Public Booking ─────────────────────────────────────────────────────────
check "Stripe webhook returns 400 without signature" \
    "curl -s -o /dev/null -w '%{http_code}' -X POST '${BASE_URL}/api/v1/webhooks/stripe' -H 'Content-Type: application/json' -d '{\"type\":\"test\"}'"  "400"

# ── Auth Flow ─────────────────────────────────────────────────────────────
check "Rate limiting active on auth endpoint" \
    "status=200; for i in \$(seq 1 12); do status=\$(curl -s -o /dev/null -w '%{http_code}' -X POST '${BASE_URL}/api/v1/auth/login' -H 'Content-Type: application/json' -d '{\"email\":\"x@x.com\",\"password\":\"x\"}'); done; echo \$status"  "429"

# ── HTTPS (production only) ────────────────────────────────────────────────
if [[ "$BASE_URL" == https://* ]]; then
    check "TLS certificate valid" \
        "curl -s -o /dev/null -w '%{http_code}' '${BASE_URL}/api/v1/health'"
    check "HTTP redirects to HTTPS" \
        "curl -s -o /dev/null -w '%{http_code}' '${BASE_URL/https:/http:}/api/v1/health'"  "301"
fi

# ── Summary ───────────────────────────────────────────────────────────────
echo ""
echo "─────────────────────────────────────────────────"
if [ ${#ERRORS[@]} -eq 0 ]; then
    echo -e "${green}✅  All ${PASS} smoke tests passed!${reset}"
    exit 0
else
    echo -e "${red}❌  ${FAIL} test(s) FAILED:${reset}"
    for err in "${ERRORS[@]}"; do
        echo -e "    ${red}•${reset} ${err}"
    done
    echo ""
    echo "Smoke test FAILED. Deployment should be rolled back."
    exit 1
fi
