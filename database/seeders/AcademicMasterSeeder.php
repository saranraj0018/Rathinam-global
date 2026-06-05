<?php

namespace Database\Seeders;

use App\Models\Discipline;
use App\Models\Role;
use App\Models\School;
use App\Models\Specialization;
use Illuminate\Database\Seeder;

class AcademicMasterSeeder extends Seeder
{
    public function run(): void
    {
        $schools = [
            'School of Engineering' => [
                'Computer Science Engineering' => [
                    'Artificial Intelligence',
                    'Machine Learning',
                    'Data Science',
                    'Cyber Security',
                    'Cloud Computing'
                ],

                'Mechanical Engineering' => [
                    'Thermal Engineering',
                    'Manufacturing Engineering',
                    'Industrial Engineering'
                ],

                'Civil Engineering' => [
                    'Structural Engineering',
                    'Construction Management',
                    'Transportation Engineering'
                ]
            ],

            'School of Management' => [
                'Business Administration' => [
                    'Finance',
                    'Marketing',
                    'Human Resource Management',
                    'Operations Management'
                ]
            ],

            'School of Science' => [
                'Physics' => [
                    'Quantum Physics',
                    'Nanotechnology',
                    'Material Science'
                ],

                'Chemistry' => [
                    'Organic Chemistry',
                    'Analytical Chemistry',
                    'Environmental Chemistry'
                ],

                'Mathematics' => [
                    'Applied Mathematics',
                    'Statistics',
                    'Computational Mathematics'
                ]
            ],

            'School of Arts and Humanities' => [
                'English' => [
                    'Literature',
                    'Linguistics',
                    'Comparative Literature'
                ],

                'History' => [
                    'Indian History',
                    'World History',
                    'Archaeology'
                ]
            ],

            'School of Education' => [
                'Education' => [
                    'Educational Technology',
                    'Curriculum Development',
                    'Teacher Education'
                ]
            ]
        ];

        foreach ($schools as $schoolName => $disciplines) {

            $school = School::firstOrCreate(
                ['name' => $schoolName],
                ['status' => 1]
            );

            foreach ($disciplines as $disciplineName => $specializations) {

                $discipline = Discipline::firstOrCreate(
                    [
                        'school_id' => $school->id,
                        'name'      => $disciplineName
                    ],
                    [
                        'status' => 1
                    ]
                );

                foreach ($specializations as $specializationName) {

                    Specialization::firstOrCreate(
                        [
                            'discipline_id' => $discipline->id,
                            'name'          => $specializationName
                        ],
                        [
                            'status' => 1
                        ]
                    );
                }
            }
        }
    }
}
