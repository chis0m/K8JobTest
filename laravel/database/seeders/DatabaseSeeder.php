<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedUsers();
        $this->seedProducts();
    }

    private function seedUsers()
    {
        User::query()->firstOrCreate(
            ['email' => 'ejimchisom@gmail.com'],
            ['name' => 'Chisom Ejim', 'password' => bcrypt('password')]
        );
        User::query()->firstOrCreate(
            ['email' => 'entochsoft@gmail.com'],
            ['name' => 'Praise Ejim', 'password' => bcrypt('password')]
        );

        echo "Users seeded successfully\n";
    }

    private function seedProducts(): void
    {
        $s3FilePath = 'test_folder/test-data.csv';

        try {
            $csvContent = Storage::disk('s3')->get($s3FilePath);
            $rows = array_map('str_getcsv', explode("\n", $csvContent));
            $header = array_shift($rows);

            foreach ($rows as $row) {
                if (count($row) > 1) {
                    $row = array_pad($row, count($header), null);
                    $data = array_combine($header, $row);
                    Log::info('Processing product:', ['number' => $data['number']]);

                    Product::query()->firstOrCreate(
                        ['number' => $data['number']],
                        [
                            'region_name'       => $data['region_name'],
                            'country_name'      => $data['country_name'],
                            'registration_name' => $data['registration_name'],
                            'status_number'     => $data['status_number'],
                            'start_date'        => $this->validateDate($data['start_date']),
                            'submission_date'   => $this->validateDate($data['submition_date']),
                            'decision_date'     => $this->validateDate($data['decision_date']),
                            'type_name'         => $data['type_name'],
                            'name'              => $data['name'],
                            'rimsys_number'     => $data['rimsys_number'],
                            'version'           => $data['version'],
                            'model'             => $data['model'],
                            'part'              => $data['part'],
                            'catalog_number'    => $data['catalog_number'],
                            'sku'               => $data['sku'],
                            'description'       => $data['description'],
                            'created_at'        => Carbon::now(),
                            'updated_at'        => Carbon::now(),
                        ]
                    );
                }
            }
            Log::info("Products seeded successfully");
        } catch (\Exception $e) {
            Log::error('Error seeding products:', ['error' => $e->getMessage()]);
            echo "Error seeding products: " . $e->getMessage() . "\n";
        }
    }

    private function validateDate($date): ?Carbon
    {
        try {
            return ($date && strtotime($date) !== false) ? Carbon::parse($date) : null;
        } catch (\Exception $e) {
            Log::warning('Failed to parse date:', ['date' => $date, 'error' => $e->getMessage()]);
            return null;
        }
    }
}
