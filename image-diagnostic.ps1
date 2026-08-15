#!/usr/bin/env pwsh

# 🔧 HOSTINGER IMAGE DIAGNOSTIC & FIX SCRIPT
# Usage: .\image-diagnostic.ps1 -Mode analyze|fix|deep

param(
    [string]$Mode = "analyze",
    [string]$SSHHost = "u157843933@sablontopilampung.com",
    [string]$ProjectPath = "/home/u157843933/domains/sablontopilampung.com/public_html"
)

function PrintHeader {
    param([string]$Text)
    Write-Host "`n========================================" -ForegroundColor Cyan
    Write-Host "🔧 $Text" -ForegroundColor Cyan
    Write-Host "========================================`n" -ForegroundColor Cyan
}

function PrintSuccess {
    param([string]$Text)
    Write-Host "✅ $Text" -ForegroundColor Green
}

function PrintError {
    param([string]$Text)
    Write-Host "❌ $Text" -ForegroundColor Red
}

function PrintWarning {
    param([string]$Text)
    Write-Host "⚠️  $Text" -ForegroundColor Yellow
}

function PrintInfo {
    param([string]$Text)
    Write-Host "ℹ️  $Text" -ForegroundColor Cyan
}

# ============================================
# MODE 1: LOCAL ANALYZE (Laravel Command)
# ============================================
if ($Mode -eq "analyze" -or $Mode -eq "deep") {
    PrintHeader "LOCAL ANALYSIS VIA LARAVEL"
    
    if ($Mode -eq "deep") {
        Write-Host "Running: php artisan images:analyze --deep`n" -ForegroundColor Yellow
        & php artisan images:analyze --deep
    } else {
        Write-Host "Running: php artisan images:analyze`n" -ForegroundColor Yellow
        & php artisan images:analyze
    }
}

# ============================================
# MODE 2: PRODUCTION DIAGNOSIS (SSH)
# ============================================
if ($Mode -eq "fix") {
    PrintHeader "PRODUCTION DIAGNOSIS VIA SSH"
    
    Write-Host "Connecting to: $SSHHost`n" -ForegroundColor Yellow
    
    # List of diagnostic commands
    $diagnosticScript = @"
#!/bin/bash
set -e
cd $ProjectPath

echo "📁 STEP 1: Check storage structure"
echo "=================================="
echo ""
echo "Variant files (first 5):"
ls -la storage/app/public/variants/ 2>/dev/null | head -6 || echo "❌ No variants directory"
echo ""
echo "Product files (first 5):"
ls -la storage/app/public/products/ 2>/dev/null | head -6 || echo "❌ No products directory"
echo ""

echo "🔗 STEP 2: Check symlink status"
echo "================================"
echo ""
ls -la public/storage 2>/dev/null || echo "❌ No symlink found"
echo ""
if [ -L public/storage ]; then
    TARGET=\$(readlink public/storage)
    echo "Symlink points to: \$TARGET"
    if [ "\$TARGET" = "../storage/app/public" ]; then
        echo "✅ Correct symlink"
    else
        echo "⚠️  Symlink target might be wrong"
    fi
fi
echo ""

echo "📊 STEP 3: Permission check"
echo "=============================="
echo ""
stat storage/app/public/variants/ 2>/dev/null | grep Access || echo "Check permission"
echo ""

echo "🧪 STEP 4: Test image accessibility"
echo "====================================="
echo ""
if [ -d storage/app/public/variants/ ]; then
    TESTFILE=\$(find storage/app/public/variants -type f | head -1)
    if [ ! -z "\$TESTFILE" ]; then
        FILENAME=\$(basename "\$TESTFILE")
        echo "Testing file: \$FILENAME"
        
        if [ -f "public/storage/variants/\$FILENAME" ]; then
            echo "✅ File accessible via symlink"
            echo "   Symlink path: public/storage/variants/\$FILENAME"
        else
            echo "❌ File NOT accessible via symlink"
        fi
        
        # Try via web
        HTTP_CODE=\$(curl -s -o /dev/null -w "%{http_code}" "https://sablontopilampung.com/storage/variants/\$FILENAME")
        echo "   HTTP Status: \$HTTP_CODE"
        if [ "\$HTTP_CODE" = "200" ]; then
            echo "   ✅ Accessible via web"
        else
            echo "   ❌ NOT accessible via web (symlink broken?)"
        fi
    fi
fi
echo ""

echo "📝 STEP 5: Check Laravel logs for errors"
echo "=========================================="
echo ""
tail -20 storage/logs/laravel.log | grep -i "image\|store" || echo "No recent image errors"
echo ""

echo "💾 STEP 6: Disk space check"
echo "============================"
echo ""
df -h /home | grep /home
echo ""

echo "✨ Diagnostic complete!"
"@

    # Save script to temp file
    $scriptPath = [System.IO.Path]::GetTempFileName() + ".sh"
    $diagnosticScript | Out-File -FilePath $scriptPath -Encoding UTF8 -Force
    
    # Run via SSH
    Get-Content $scriptPath | ssh $SSHHost
    
    # Cleanup
    Remove-Item $scriptPath -Force
}

PrintHeader "DIAGNOSTIC SUMMARY"
Write-Host @"
Next steps:

1️⃣  If files are MISSING from storage:
    - Re-upload images via admin panel
    - Check upload controller for errors

2️⃣  If symlink is BROKEN:
    - SSH to production
    - Run: rm -rf public/storage && php artisan storage:link
    
3️⃣  If permission is WRONG:
    - SSH to production  
    - Run: chmod -R 775 storage/app/public

4️⃣  If everything OK but still 404:
    - Clear Laravel cache: php artisan optimize:clear
    - Clear browser cache: Ctrl+Shift+Delete
    - Refresh page

For more details, see: ANALISIS_ERROR_GAMBAR.md
"@ -ForegroundColor Cyan

