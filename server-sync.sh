#!/bin/bash
# Server sync script untuk Hostinger

cd ~/public_html

echo "🔍 Checking status..."
git status

echo ""
echo "🧹 Cleaning untracked files..."
# Discard changes to tracked files (cache)
git restore bootstrap/cache/packages.php
git restore bootstrap/cache/services.php

echo ""
echo "📥 Hard reset ke remote..."
git reset --hard origin/hostinger

echo ""
echo "✅ Status after sync:"
git status

echo ""
echo "📊 Recent commits:"
git log --oneline -3

echo "🎉 Server is now synced with remote!"
