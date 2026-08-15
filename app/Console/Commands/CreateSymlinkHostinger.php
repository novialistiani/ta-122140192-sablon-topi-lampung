<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateSymlinkHostinger extends Command
{
    protected $signature = 'symlink:create {--cPanel : Use cPanel API if symlink() fails}';
    protected $description = 'Create storage symlink without using exec() (for Hostinger)';

    public function handle()
    {
        $this->line('🔗 Creating symlink for Hostinger...\n');

        $linkPath = public_path('storage');
        $targetPath = storage_path('app/public');

        // Step 1: Check if symlink already exists
        if (is_link($linkPath)) {
            $target = readlink($linkPath);
            $this->info("✅ Symlink already exists: {$linkPath} -> {$target}");
            return 0;
        }

        // Step 2: Remove existing directory/file if any
        if (is_dir($linkPath) || is_file($linkPath)) {
            $this->warn("⚠️  Found existing directory/file at {$linkPath}, removing...");
            if (!$this->removeDirectory($linkPath)) {
                $this->error("❌ Failed to remove {$linkPath}");
                return 1;
            }
            $this->info("✅ Removed old directory/file");
        }

        // Step 3: Verify target directory exists
        if (!is_dir($targetPath)) {
            $this->error("❌ Target directory not found: {$targetPath}");
            return 1;
        }
        $this->info("✅ Target directory exists: {$targetPath}");

        // Step 4: Try to create symlink using PHP symlink() function
        $this->line("\n📝 Attempting to create symlink using symlink() function...");
        
        // Use relative path for better compatibility
        $relativeTarget = $this->getRelativePath($linkPath, $targetPath);
        $this->line("   From: {$linkPath}");
        $this->line("   To: {$relativeTarget} (relative)");

        try {
            if (@symlink($relativeTarget, $linkPath)) {
                $this->info("✅ Symlink created successfully!");
                
                // Verify
                if (is_link($linkPath)) {
                    $verifyTarget = readlink($linkPath);
                    $this->info("✅ Verified: {$linkPath} -> {$verifyTarget}");
                    return 0;
                }
            } else {
                $this->warn("⚠️  symlink() returned false");
            }
        } catch (\Exception $e) {
            $this->error("❌ Exception: " . $e->getMessage());
        }

        // Step 5: Try alternative method - create via .htaccess (for shared hosting)
        $this->line("\n📝 Trying alternative method: .htaccess alias...");
        if ($this->createHtaccessAlias($linkPath, $targetPath)) {
            $this->info("✅ Created .htaccess alias (alternative to symlink)");
            return 0;
        }

        // Step 6: Last resort - cPanel API (if enabled)
        if ($this->option('cPanel')) {
            $this->line("\n📝 Trying cPanel API method...");
            if ($this->createViaHostingerCPanel($linkPath, $targetPath)) {
                $this->info("✅ Created via cPanel API");
                return 0;
            }
        }

        $this->error("\n❌ All methods failed! Manual steps required:");
        $this->error("1. Login to Hostinger cPanel");
        $this->error("2. Go to: File Manager");
        $this->error("3. Navigate to: /public_html/public");
        $this->error("4. Create symbolic link:");
        $this->error("   Link name: storage");
        $this->error("   Target: {$targetPath}");
        $this->error("5. Refresh browser cache (Ctrl+Shift+Delete)");

        return 1;
    }

    /**
     * Get relative path for symlink (better for portable setup)
     */
    private function getRelativePath($from, $to)
    {
        $from = explode('/', $from);
        $to = explode('/', $to);

        // Find common base
        $relativeArr = [];
        $count = count($from) - count($to);

        if ($count > 0) {
            $relativeArr = array_fill(0, $count, '..');
        } else {
            $relativeArr = array_fill(0, -$count, '..');
        }

        $relativeArr = array_merge($relativeArr, array_slice($to, count($to) + $count));

        return implode('/', $relativeArr);
    }

    /**
     * Alternative: Create .htaccess alias (for shared hosting where symlink doesn't work)
     */
    private function createHtaccessAlias($linkPath, $targetPath)
    {
        // Create directory first if needed
        if (!is_dir($linkPath)) {
            @mkdir($linkPath, 0755, true);
        }

        $htaccessContent = <<<'EOT'
# Allow access to storage files
<IfModule mod_alias.c>
    Alias /storage "%TARGET%"
</IfModule>

# If mod_alias not available, use mod_rewrite
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteRule ^storage/(.*)$ ../storage/app/public/$1 [L]
</IfModule>
EOT;

        $htaccessContent = str_replace('%TARGET%', $targetPath, $htaccessContent);
        $htaccessPath = $linkPath . '/.htaccess';

        if (@file_put_contents($htaccessPath, $htaccessContent)) {
            return true;
        }

        return false;
    }

    /**
     * Try via Hostinger cPanel API (requires cPanel credentials)
     */
    private function createViaHostingerCPanel($linkPath, $targetPath)
    {
        // This requires cPanel credentials which aren't available in most cases
        // But documenting for future reference
        $this->warn("⚠️  cPanel API method requires credentials, skipping");
        return false;
    }

    /**
     * Remove directory recursively
     */
    private function removeDirectory($path)
    {
        if (is_link($path)) {
            return @unlink($path);
        }

        if (!is_dir($path)) {
            return @unlink($path);
        }

        // Remove directory contents
        $files = @scandir($path);
        if ($files === false) {
            return false;
        }

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $path . '/' . $file;
                if (is_dir($filePath)) {
                    if (!$this->removeDirectory($filePath)) {
                        return false;
                    }
                } else {
                    if (!@unlink($filePath)) {
                        return false;
                    }
                }
            }
        }

        return @rmdir($path);
    }
}
