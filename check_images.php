<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

// Check products with images
$products = Product::select('id', 'name', 'image', 'images')->limit(5)->get();
echo "Total products: " . Product::count() . "\n";
echo "Products with image field filled: " . Product::whereNotNull('image')->count() . "\n";
echo "Products with images field filled: " . Product::whereNotNull('images')->count() . "\n\n";

echo "Sample products:\n";
foreach ($products as $p) {
    echo "ID: {$p->id}, Name: {$p->name}\n";
    echo "  image: " . ($p->image ? $p->image : 'NULL') . "\n";
    echo "  images: " . ($p->images ? json_encode($p->images) : 'NULL') . "\n\n";
}

// Check custom design uploads
echo "\nCustom design uploads:\n";
$uploads = \App\Models\CustomDesignUpload::select('id', 'custom_design_order_id', 'file_path')->limit(5)->get();
echo "Total uploads: " . \App\Models\CustomDesignUpload::count() . "\n";
foreach ($uploads as $u) {
    echo "ID: {$u->id}, Order: {$u->custom_design_order_id}, Path: {$u->file_path}\n";
}
?>
