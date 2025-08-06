<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\StudentPlan;
use App\Models\TicketHistory;
use App\Models\User;
use Faker\Factory as Faker;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('ja_JP');
        
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@eikaiwa.com'],
            [
                'name' => '管理者',
                'password' => bcrypt('password'),
            ]
        );

        $planNames = [
            'ベーシックプラン',
            'スタンダードプラン', 
            'プレミアムプラン',
            'ビジネス英会話プラン',
            'TOEIC対策プラン',
            '日常英会話プラン'
        ];

        for ($i = 1; $i <= 100; $i++) {
            $student = Student::create([
                'student_id' => sprintf('S%05d', $i),
                'nickname' => $faker->userName,
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'remaining_tickets' => $faker->numberBetween(0, 20),
            ]);

            StudentPlan::create([
                'student_id' => $student->id,
                'plan_name' => $faker->randomElement($planNames),
                'start_date' => $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
                'finish_date' => $faker->optional(0.3)->dateTimeBetween('now', '+1 year')?->format('Y-m-d'),
                'is_active' => true,
            ]);

            $historyCount = $faker->numberBetween(5, 10);
            for ($j = 0; $j < $historyCount; $j++) {
                TicketHistory::create([
                    'student_id' => $student->id,
                    'added_by_user_id' => $adminUser->id,
                    'ticket_count' => $faker->numberBetween(1, 10),
                    'action_type' => $faker->randomElement(['add', 'subtract']),
                    'notes' => $faker->optional(0.7)->sentence,
                    'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                ]);
            }
        }
    }
}
