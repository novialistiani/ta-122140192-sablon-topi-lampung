#!/bin/bash

# 🔍 DIAGNOSTIC SCRIPT: Debug Gambar 404 Issue di Hostinger
# Copy-paste ini ke SSH console

echo "=========================================="
echo "🔍 IMAGE 404 DIAGNOSTIC"
echo "=========================================="
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test function
test_item() {
    local name=$1
    local result=$2
    
    if [ "$result" = "OK" ]; then
        echo -e "${GREEN}✅${NC} $name"
    elif [ "$result" = "WARNING" ]; then
        echo -e "${YELLOW}⚠️${NC} $name"
    else
        echo -e "${RED}❌${NC} $name"
    fi
}

echo "📁 STEP 1: Check Symlink Status"
echo "================================"

if [ -L "public/storage" ]; then
    TARGET=$(readlink public/storage)
    echo -e "${GREEN}✅${NC} Symlink exists"
    echo "   Target: $TARGET"
    
    if [ "$TARGET" = "../storage/app/public" ]; then
        echo -e "${GREEN}✅${NC} Target is correct (relative path)"
        SYMLINK_OK="OK"
    else
        echo -e "${YELLOW}⚠️${NC} Target seems unusual: $TARGET"
        SYMLINK_OK="WARNING"
    fi
    
elif [ -d "public/storage" ]; then
    echo -e "${RED}❌${NC} public/storage is a FOLDER, not symlink"
    echo "   This is the problem! Must be symlink."
    SYMLINK_OK="FAIL"
    
else
    echo -e "${RED}❌${NC} public/storage does not exist"
    SYMLINK_OK="FAIL"
fi

echo ""
echo "📊 STEP 2: Check Files in Storage"
echo "=================================="

VARIANT_COUNT=$(find storage/app/public/variants -type f 2>/dev/null | wc -l)
PRODUCT_COUNT=$(find storage/app/public/products -type f 2>/dev/null | wc -l)

if [ "$VARIANT_COUNT" -gt 0 ]; then
    echo -e "${GREEN}✅${NC} Variants: $VARIANT_COUNT files"
else
    echo -e "${RED}❌${NC} Variants: NO FILES"
fi

if [ "$PRODUCT_COUNT" -gt 0 ]; then
    echo -e "${GREEN}✅${NC} Products: $PRODUCT_COUNT files"
else
    echo -e "${RED}❌${NC} Products: NO FILES"
fi

echo ""
echo "🔗 STEP 3: Test File Accessibility via Symlink"
echo "=============================================="

if [ "$VARIANT_COUNT" -gt 0 ]; then
    TESTFILE=$(find storage/app/public/variants -type f | head -1)
    FILENAME=$(basename "$TESTFILE")
    
    echo "Test file: $FILENAME"
    
    # Test 1: Via symlink path
    if [ -f "public/storage/variants/$FILENAME" ]; then
        echo -e "${GREEN}✅${NC} File accessible via symlink"
        SYMLINK_ACCESS="OK"
    else
        echo -e "${RED}❌${NC} File NOT accessible via symlink"
        echo "   Expected: public/storage/variants/$FILENAME"
        SYMLINK_ACCESS="FAIL"
    fi
    
    # Test 2: Via direct storage path
    if [ -f "storage/app/public/variants/$FILENAME" ]; then
        echo -e "${GREEN}✅${NC} File exists at direct path"
    else
        echo -e "${RED}❌${NC} File NOT at direct path"
    fi
else
    echo -e "${YELLOW}⚠️${NC} No variant files to test"
fi

echo ""
echo "🔑 STEP 4: Check Permissions"
echo "=============================="

STORAGE_PERM=$(ls -ld storage/app/public/variants/ 2>/dev/null | awk '{print $1}')
echo "Storage variants permission: $STORAGE_PERM"

if [[ "$STORAGE_PERM" =~ r.*r ]]; then
    echo -e "${GREEN}✅${NC} Permission allows read"
else
    echo -e "${RED}❌${NC} Permission might block read"
    echo "   Fix: chmod -R 755 storage/app/public"
fi

echo ""
echo "🌐 STEP 5: Test HTTP Access"
echo "============================="

if [ ! -z "$TESTFILE" ]; then
    FILENAME=$(basename "$TESTFILE")
    echo "Testing: https://sablontopilampung.com/storage/variants/$FILENAME"
    
    # Try to get HTTP status
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://sablontopilampung.com/storage/variants/$FILENAME" 2>/dev/null)
    
    if [ "$HTTP_CODE" = "200" ]; then
        echo -e "${GREEN}✅${NC} HTTP/2 200 OK - File accessible!"
        HTTP_TEST="OK"
    elif [ "$HTTP_CODE" = "404" ]; then
        echo -e "${RED}❌${NC} HTTP/2 404 Not Found"
        HTTP_TEST="FAIL"
    elif [ "$HTTP_CODE" = "403" ]; then
        echo -e "${RED}❌${NC} HTTP/2 403 Forbidden (permission issue?)"
        HTTP_TEST="FAIL"
    else
        echo -e "${YELLOW}⚠️${NC} HTTP/$HTTP_CODE"
        HTTP_TEST="WARNING"
    fi
else
    echo -e "${YELLOW}⚠️${NC} No test file available"
fi

echo ""
echo "📝 STEP 6: Check Laravel Logs"
echo "============================="

ERRORS=$(tail -50 storage/logs/laravel.log 2>/dev/null | grep -i "error\|exception" | wc -l)

if [ "$ERRORS" -gt 0 ]; then
    echo -e "${YELLOW}⚠️${NC} Found $ERRORS errors in logs"
    echo "Recent errors:"
    tail -50 storage/logs/laravel.log 2>/dev/null | grep -i "error\|exception" | head -3
else
    echo -e "${GREEN}✅${NC} No recent errors in logs"
fi

echo ""
echo "=========================================="
echo "📋 SUMMARY"
echo "=========================================="
echo ""

ISSUES=0

if [ "$SYMLINK_OK" != "OK" ]; then
    echo -e "${RED}❌ ISSUE 1: Symlink problem${NC}"
    echo "   Solution: rm -rf public/storage && cd public && ln -s ../storage/app/public storage && cd .."
    ISSUES=$((ISSUES + 1))
fi

if [ "$VARIANT_COUNT" -eq 0 ]; then
    echo -e "${RED}❌ ISSUE 2: No files in storage${NC}"
    echo "   Solution: Upload images via admin panel"
    ISSUES=$((ISSUES + 1))
fi

if [ "$SYMLINK_ACCESS" = "FAIL" ]; then
    echo -e "${RED}❌ ISSUE 3: Symlink not working${NC}"
    echo "   Solution: Fix permission: chmod -R 755 storage/app/public"
    ISSUES=$((ISSUES + 1))
fi

if [ "$HTTP_TEST" = "FAIL" ]; then
    echo -e "${RED}❌ ISSUE 4: HTTP 404 error${NC}"
    echo "   Likely caused by issues above"
    ISSUES=$((ISSUES + 1))
fi

if [ "$ISSUES" -eq 0 ]; then
    echo -e "${GREEN}✅ NO ISSUES FOUND${NC}"
    echo ""
    echo "Everything looks good! But gambar masih tidak muncul?"
    echo "Try:"
    echo "  1. php artisan optimize:clear"
    echo "  2. php artisan config:cache"
    echo "  3. Clear browser cache (Ctrl+Shift+Delete)"
    echo "  4. Refresh halaman"
else
    echo ""
    echo "Found $ISSUES issue(s) to fix"
fi

echo ""
echo "=========================================="
