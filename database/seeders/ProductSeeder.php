<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // AC Services
            [
                'name' => 'AC Installation',
                'description' => 'Complete air conditioning system installation',
                'sku' => 'AC-INST-001',
                'price' => 2500.00,
                'unit' => 'unit',
                'category' => 'AC Services',
                'is_service' => true,
                'is_active' => true
            ],
            [
                'name' => 'AC Maintenance',
                'description' => 'Regular AC system maintenance and cleaning',
                'sku' => 'AC-MAINT-001',
                'price' => 150.00,
                'unit' => 'visit',
                'category' => 'AC Services',
                'is_service' => true,
                'is_active' => true
            ],
            [
                'name' => 'AC Repair',
                'description' => 'AC system repair and troubleshooting',
                'sku' => 'AC-REPAIR-001',
                'price' => 85.00,
                'unit' => 'hour',
                'category' => 'AC Services',
                'is_service' => true,
                'is_active' => true
            ],

            // Electrical Services
            [
                'name' => 'Electrical Wiring',
                'description' => 'Complete electrical wiring installation',
                'sku' => 'ELEC-WIRE-001',
                'price' => 125.00,
                'unit' => 'hour',
                'category' => 'Electrical Services',
                'is_service' => true,
                'is_active' => true
            ],
            [
                'name' => 'Electrical Panel Installation',
                'description' => 'Electrical panel and breaker installation',
                'sku' => 'ELEC-PANEL-001',
                'price' => 800.00,
                'unit' => 'unit',
                'category' => 'Electrical Services',
                'is_service' => true,
                'is_active' => true
            ],
            [
                'name' => 'Electrical Troubleshooting',
                'description' => 'Electrical system diagnosis and repair',
                'sku' => 'ELEC-TROUBLE-001',
                'price' => 95.00,
                'unit' => 'hour',
                'category' => 'Electrical Services',
                'is_service' => true,
                'is_active' => true
            ],

            // Carpentry Services
            [
                'name' => 'Cabinet Installation',
                'description' => 'Custom cabinet design and installation',
                'sku' => 'CARP-CAB-001',
                'price' => 200.00,
                'unit' => 'linear foot',
                'category' => 'Carpentry Services',
                'is_service' => true,
                'is_active' => true
            ],
            [
                'name' => 'Door Installation',
                'description' => 'Interior and exterior door installation',
                'sku' => 'CARP-DOOR-001',
                'price' => 350.00,
                'unit' => 'unit',
                'category' => 'Carpentry Services',
                'is_service' => true,
                'is_active' => true
            ],
            [
                'name' => 'Flooring Installation',
                'description' => 'Hardwood and laminate flooring installation',
                'sku' => 'CARP-FLOOR-001',
                'price' => 8.50,
                'unit' => 'sq ft',
                'category' => 'Carpentry Services',
                'is_service' => true,
                'is_active' => true
            ],

            // Painting Services
            [
                'name' => 'Interior Painting',
                'description' => 'Interior wall and ceiling painting',
                'sku' => 'PAINT-INT-001',
                'price' => 3.50,
                'unit' => 'sq ft',
                'category' => 'Painting Services',
                'is_service' => true,
                'is_active' => true
            ],
            [
                'name' => 'Exterior Painting',
                'description' => 'Exterior wall and trim painting',
                'sku' => 'PAINT-EXT-001',
                'price' => 4.25,
                'unit' => 'sq ft',
                'category' => 'Painting Services',
                'is_service' => true,
                'is_active' => true
            ],
            [
                'name' => 'Paint Materials',
                'description' => 'High-quality paint and primer',
                'sku' => 'PAINT-MAT-001',
                'price' => 45.00,
                'unit' => 'gallon',
                'category' => 'Painting Materials',
                'is_service' => false,
                'is_active' => true
            ],

            // Materials and Supplies
            [
                'name' => 'Electrical Wire (12 AWG)',
                'description' => '12 gauge electrical wire',
                'sku' => 'WIRE-12AWG-001',
                'price' => 1.25,
                'unit' => 'foot',
                'category' => 'Electrical Materials',
                'is_service' => false,
                'is_active' => true
            ],
            [
                'name' => 'AC Filter',
                'description' => 'Standard HVAC air filter',
                'sku' => 'AC-FILTER-001',
                'price' => 25.00,
                'unit' => 'piece',
                'category' => 'AC Materials',
                'is_service' => false,
                'is_active' => true
            ],
            [
                'name' => 'Wood Screws',
                'description' => 'Assorted wood screws pack',
                'sku' => 'SCREW-WOOD-001',
                'price' => 12.50,
                'unit' => 'pack',
                'category' => 'Carpentry Materials',
                'is_service' => false,
                'is_active' => true
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
