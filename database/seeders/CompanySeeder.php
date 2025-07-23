<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::create([
            'name' => 'Noor Alfath',
            'address' => '123 Business Street, Business District, City 12345',
            'phone' => '+1 (555) 123-4567',
            'email' => 'info@nooralfath.com',
            'website' => 'www.nooralfath.com',
            'tax_number' => 'TAX123456789',
            'registration_number' => 'REG987654321',
            'bank_details' => json_encode([
                'bank_name' => 'First National Bank',
                'account_number' => '1234567890',
                'routing_number' => '987654321',
                'swift_code' => 'FNBKUS33'
            ])
        ]);
    }
}
