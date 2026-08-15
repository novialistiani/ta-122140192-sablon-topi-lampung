<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomDesignPrice;

class CustomDesignPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Upload Sections
        $uploadSections = [
            ['code' => 'A', 'name' => 'A (Dada depan horizontal, uk. A4)', 'price' => 50000],
            ['code' => 'B', 'name' => 'B (Gambar kantong kiri, uk. 10x10 cm)', 'price' => 25000],
            ['code' => 'C', 'name' => 'C (Dada siku kanan, uk. 10x10 cm)', 'price' => 25000],
            ['code' => 'D', 'name' => 'D (Dada depan vertikal, uk. A4)', 'price' => 50000],
            ['code' => 'E', 'name' => 'E (Punggung belakang vertikal, uk. A4)', 'price' => 50000],
            ['code' => 'F', 'name' => 'F (Punggung siku kanan, uk. 10x10 cm)', 'price' => 25000],
            ['code' => 'G', 'name' => 'G (Dada depan horizontal, uk. A3)', 'price' => 75000],
            ['code' => 'H', 'name' => 'H (Dada depan ver sisi, uk. A3)', 'price' => 75000],
            ['code' => 'I', 'name' => 'I (Punggung belakang horizontal, uk. A4)', 'price' => 50000],
            ['code' => 'J', 'name' => 'J (Punggung belakang horizontal, uk. A3)', 'price' => 75000],
        ];

        foreach ($uploadSections as $section) {
            CustomDesignPrice::updateOrCreate(
                ['type' => 'upload_section', 'code' => $section['code']],
                [
                    'name' => $section['name'],
                    'price' => $section['price'],
                    'is_active' => true
                ]
            );
        }

        // Cutting Types
        $cuttingTypes = [
            ['code' => 'cutting-pvc-flex', 'name' => 'Cutting PVC Flex', 'price' => 30000],
            ['code' => 'printable', 'name' => 'Printable', 'price' => 40000],
        ];

        foreach ($cuttingTypes as $cutting) {
            CustomDesignPrice::updateOrCreate(
                ['type' => 'cutting_type', 'code' => $cutting['code']],
                [
                    'name' => $cutting['name'],
                    'price' => $cutting['price'],
                    'is_active' => true
                ]
            );
        }

        $this->command->info('Custom Design Prices seeded successfully!');
        $this->command->info('Total upload sections: ' . count($uploadSections));
        $this->command->info('Total cutting types: ' . count($cuttingTypes));
    }
}
