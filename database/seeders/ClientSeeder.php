<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'name' => 'John Smith',
                'company_name' => 'ABC Corporation',
                'email' => 'john.smith@abc.com',
                'phone' => '+1 (555) 234-5678',
                'address' => '456 Corporate Ave',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'USA',
                'notes' => 'Preferred client for AC installation services'
            ],
            [
                'name' => 'Sarah Johnson',
                'company_name' => 'XYZ Industries',
                'email' => 'sarah.j@xyz.com',
                'phone' => '+1 (555) 345-6789',
                'address' => '789 Industrial Blvd',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'postal_code' => '90210',
                'country' => 'USA',
                'notes' => 'Regular electrical maintenance contracts'
            ],
            [
                'name' => 'Michael Davis',
                'company_name' => 'Tech Solutions LLC',
                'email' => 'm.davis@techsolutions.com',
                'phone' => '+1 (555) 456-7890',
                'address' => '321 Tech Park Dr',
                'city' => 'Chicago',
                'state' => 'IL',
                'postal_code' => '60601',
                'country' => 'USA',
                'notes' => 'Office renovation and painting projects'
            ],
            [
                'name' => 'Emily Wilson',
                'company_name' => 'Green Energy Co.',
                'email' => 'emily.w@greenenergy.com',
                'phone' => '+1 (555) 567-8901',
                'address' => '654 Sustainable Way',
                'city' => 'Austin',
                'state' => 'TX',
                'postal_code' => '73301',
                'country' => 'USA',
                'notes' => 'HVAC and electrical efficiency projects'
            ],
            [
                'name' => 'Robert Brown',
                'company_name' => 'Brown Construction',
                'email' => 'r.brown@brownconstruction.com',
                'phone' => '+1 (555) 678-9012',
                'address' => '987 Builder St',
                'city' => 'Miami',
                'state' => 'FL',
                'postal_code' => '33101',
                'country' => 'USA',
                'notes' => 'Carpentry and electrical subcontracting'
            ]
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }
    }
}
