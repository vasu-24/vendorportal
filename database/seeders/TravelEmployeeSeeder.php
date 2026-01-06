<?php

namespace Database\Seeders;

use App\Models\TravelEmployee;
use Illuminate\Database\Seeder;

class TravelEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'employee_name' => 'Sujith Nair',
                'employee_code' => 'EMP001',
                'tag_id' => 'G100',
                'tag_name' => 'G100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Ravi Prakash',
                'employee_code' => 'EMP002',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Anirban Sinha',
                'employee_code' => 'EMP003',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Rajeesh R Menon',
                'employee_code' => 'EMP004',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Bharath Shankar Ganapathy',
                'employee_code' => 'EMP005',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Ansha Dixit',
                'employee_code' => 'EMP006',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Geolly Petreshia R',
                'employee_code' => 'EMP007',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Aliya Fathima Sheriff',
                'employee_code' => 'EMP008',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Eryn Reva Wali',
                'employee_code' => 'EMP009',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Siddharth Shetty',
                'employee_code' => 'EMP010',
                'tag_id' => 'G100',
                'tag_name' => 'G100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Pramod K Varma',
                'employee_code' => 'EMP011',
                'tag_id' => 'G100',
                'tag_name' => 'G100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Sonam Rai',
                'employee_code' => 'EMP012',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Dhiraj Shetty',
                'employee_code' => 'EMP013',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Siddharth Singh',
                'employee_code' => 'EMP014',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Abhishek Dhruv Sankritik',
                'employee_code' => 'EMP015',
                'tag_id' => 'F100',
                'tag_name' => 'F100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Tanmoy Adhikary',
                'employee_code' => 'EMP016',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Mythili Anish Kamath',
                'employee_code' => 'EMP017',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Arnav Sahni',
                'employee_code' => 'EMP018',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Nirmal N R',
                'employee_code' => 'EMP019',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Taanishi Kohli',
                'employee_code' => 'EMP020',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Arjun Subhash Kumar',
                'employee_code' => 'EMP021',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Yohann Dsouza',
                'employee_code' => 'EMP022',
                'tag_id' => 'F100',
                'tag_name' => 'F100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Sanmesh Mahesh Kalyanpur',
                'employee_code' => 'EMP023',
                'tag_id' => 'F100',
                'tag_name' => 'F100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Akanksha Sarma',
                'employee_code' => 'EMP024',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Gauri Kalyanpur',
                'employee_code' => 'EMP025',
                'tag_id' => 'F100',
                'tag_name' => 'F100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Anusree Jayakrishnan',
                'employee_code' => 'EMP026',
                'tag_id' => 'TC100',
                'tag_name' => 'TC100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Vineet Nair',
                'employee_code' => 'EMP027',
                'tag_id' => 'F100',
                'tag_name' => 'F100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Radha Rani Kizhanattam',
                'employee_code' => 'EMP028',
                'tag_id' => 'G100',
                'tag_name' => 'G100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Mayuresh Anil Nirhali',
                'employee_code' => 'EMP029',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Bachi Allamsetty',
                'employee_code' => 'EMP030',
                'tag_id' => 'B100',
                'tag_name' => 'B100',
                'is_active' => true,
            ],
            [
                'employee_name' => 'Vikram Modi',
                'employee_code' => 'EMP031',
                'tag_id' => 'G100',
                'tag_name' => 'G100/F100',
                'is_active' => true,
                // Note: 40% towards G100 and 60% towards F100
            ],
        ];

        foreach ($employees as $employee) {
            TravelEmployee::updateOrCreate(
                ['employee_name' => $employee['employee_name']],
                $employee
            );
        }

        $this->command->info('✅ ' . count($employees) . ' Travel Employees seeded successfully!');
    }
}