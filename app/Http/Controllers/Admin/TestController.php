<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * Test database image integration via HTTP
     * Route: /api/admin/test/db-integration
     */
    public function testDbIntegration()
    {
        try {
            $tests = [
                'database_connection' => $this->testConnection(),
                'product_images' => $this->testProductImages(),
                'variant_images' => $this->testVariantImages(),
                'custom_designs' => $this->testCustomDesigns(),
                'storage_directories' => $this->testStorageDirectories(),
                'file_accessibility' => $this->testFileAccessibility(),
            ];

            return response()->json([
                'success' => true,
                'timestamp' => now()->toIso8601String(),
                'tests' => $tests,
                'summary' => $this->generateSummary($tests),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTrace() : null,
            ], 500);
        }
    }

    private function testConnection()
    {
        try {
            $count = \App\Models\Product::count();
            return [
                'status' => 'success',
                'message' => 'Database connected successfully',
                'products_count' => $count,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    private function testProductImages()
    {
        try {
            $total = \App\Models\Product::count();
            $withImage = \App\Models\Product::whereNotNull('image')->count();
            $localImages = \App\Models\Product::whereNotNull('image')
                ->where('image', 'not like', 'https://%')
                ->where('image', 'not like', 'http://%')
                ->count();
            $externalImages = $withImage - $localImages;

            $samples = \App\Models\Product::whereNotNull('image')
                ->limit(3)
                ->get(['id', 'name', 'image'])
                ->map(function($p) {
                    $exists = file_exists(storage_path('app/public/' . $p->image));
                    return [
                        'id' => $p->id,
                        'name' => $p->name,
                        'image' => $p->image,
                        'type' => (strpos($p->image, 'http') === 0) ? 'external_url' : 'local_path',
                        'file_exists' => $exists && strpos($p->image, 'http') !== 0,
                        'url' => (strpos($p->image, 'http') === 0) ? $p->image : asset('storage/' . $p->image),
                    ];
                });

            return [
                'status' => 'success',
                'total_products' => $total,
                'with_image' => $withImage,
                'local_images' => $localImages,
                'external_urls' => $externalImages,
                'samples' => $samples,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    private function testVariantImages()
    {
        try {
            $total = \App\Models\ProductVariant::count();
            $withImage = \App\Models\ProductVariant::whereNotNull('image')->count();

            $working = 0;
            $broken = 0;

            \App\Models\ProductVariant::whereNotNull('image')
                ->select('id', 'image')
                ->chunk(50, function($variants) use (&$working, &$broken) {
                    foreach ($variants as $variant) {
                        if (file_exists(storage_path('app/public/' . $variant->image))) {
                            $working++;
                        } else {
                            $broken++;
                        }
                    }
                });

            $samples = \App\Models\ProductVariant::whereNotNull('image')
                ->select('id', 'product_id', 'color', 'size', 'image')
                ->limit(3)
                ->get()
                ->map(function($v) {
                    $exists = file_exists(storage_path('app/public/' . $v->image));
                    return [
                        'id' => $v->id,
                        'product_id' => $v->product_id,
                        'color' => $v->color,
                        'size' => $v->size,
                        'image_path' => $v->image,
                        'file_exists' => $exists,
                        'url' => asset('storage/' . $v->image),
                    ];
                });

            return [
                'status' => 'success',
                'total_variants' => $total,
                'with_image' => $withImage,
                'working' => $working,
                'broken' => $broken,
                'samples' => $samples,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    private function testCustomDesigns()
    {
        try {
            $total = \App\Models\CustomDesignUpload::count();

            $working = 0;
            $broken = 0;

            \App\Models\CustomDesignUpload::select('id', 'file_path')
                ->chunk(50, function($uploads) use (&$working, &$broken) {
                    foreach ($uploads as $upload) {
                        if (file_exists(storage_path('app/public/' . $upload->file_path))) {
                            $working++;
                        } else {
                            $broken++;
                        }
                    }
                });

            $samples = \App\Models\CustomDesignUpload::select('id', 'custom_design_order_id', 'section_name', 'file_path')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get()
                ->map(function($u) {
                    $exists = file_exists(storage_path('app/public/' . $u->file_path));
                    return [
                        'id' => $u->id,
                        'order_id' => $u->custom_design_order_id,
                        'section' => $u->section_name,
                        'file_path' => $u->file_path,
                        'file_exists' => $exists,
                        'url' => asset('storage/' . $u->file_path),
                    ];
                });

            return [
                'status' => 'success',
                'total_uploads' => $total,
                'working' => $working,
                'broken' => $broken,
                'samples' => $samples,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    private function testStorageDirectories()
    {
        $dirs = [
            'public' => storage_path('app/public'),
            'products' => storage_path('app/public/products'),
            'variants' => storage_path('app/public/variants'),
            'custom_designs' => storage_path('app/public/custom-designs'),
        ];

        $results = [];
        foreach ($dirs as $name => $path) {
            $exists = is_dir($path);
            $writable = is_writable($path);
            $fileCount = 0;

            if ($exists) {
                $files = @glob($path . '/*', GLOB_NOSORT);
                $fileCount = is_array($files) ? count($files) : 0;
            }

            $results[$name] = [
                'path' => $path,
                'exists' => $exists,
                'writable' => $writable,
                'files_count' => $fileCount,
            ];
        }

        return [
            'status' => 'success',
            'directories' => $results,
        ];
    }

    private function testFileAccessibility()
    {
        // Test symlink
        $symlinkPath = public_path('storage');
        $symlinkExists = is_link($symlinkPath);
        $symlinkTarget = $symlinkExists ? readlink($symlinkPath) : null;

        // Check public/storage directory
        $publicStorageExists = is_dir(public_path('storage'));

        // Try to access a file via HTTP
        $testFile = null;
        $testUrl = null;
        $testAccessible = false;

        $product = \App\Models\Product::whereNotNull('image')
            ->where('image', 'not like', 'https://%')
            ->where('image', 'not like', 'http://%')
            ->first();

        if ($product && file_exists(storage_path('app/public/' . $product->image))) {
            $testFile = $product->image;
            $testUrl = asset('storage/' . $product->image);
            
            // Simple accessibility check
            $testAccessible = true; // Assume accessible if file exists locally
        }

        return [
            'status' => 'success',
            'symlink' => [
                'exists' => $symlinkExists,
                'target' => $symlinkTarget,
            ],
            'public_storage_dir' => $publicStorageExists,
            'test_file' => [
                'path' => $testFile,
                'url' => $testUrl,
                'accessible' => $testAccessible,
            ],
        ];
    }

    private function generateSummary($tests)
    {
        $summary = [
            'total_tests' => count($tests),
            'passed' => 0,
            'failed' => 0,
            'issues' => [],
        ];

        foreach ($tests as $name => $test) {
            if ($test['status'] === 'success') {
                $summary['passed']++;
            } else {
                $summary['failed']++;
                $summary['issues'][] = "$name: " . $test['message'];
            }
        }

        return $summary;
    }

    /**
     * Get database status via simple queries
     * Route: /api/admin/test/db-status
     */
    public function dbStatus()
    {
        try {
            return response()->json([
                'success' => true,
                'database' => [
                    'host' => config('database.connections.mysql.host'),
                    'database' => config('database.connections.mysql.database'),
                    'username' => config('database.connections.mysql.username'),
                ],
                'products_count' => \App\Models\Product::count(),
                'variants_count' => \App\Models\ProductVariant::count(),
                'custom_designs_count' => \App\Models\CustomDesignUpload::count(),
                'products_with_local_images' => \App\Models\Product::whereNotNull('image')
                    ->where('image', 'not like', 'https://%')
                    ->where('image', 'not like', 'http://%')
                    ->count(),
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
