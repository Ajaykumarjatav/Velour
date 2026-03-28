<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientNote;
use App\Models\ClientFormula;
use App\Models\Salon;
use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ClientSeeder extends Seeder
{
    // Realistic client data pool
    private array $firstNames = [
        'Sophie', 'Charlotte', 'Amelia', 'Olivia', 'Isabella', 'Evelyn', 'Scarlett',
        'Victoria', 'Penelope', 'Luna', 'Grace', 'Chloe', 'Lily', 'Hannah', 'Zoe',
        'Natalie', 'Samantha', 'Eleanor', 'Claire', 'Emma', 'Julia', 'Rebecca',
        'Katherine', 'Catherine', 'Alexandra', 'Stephanie', 'Jennifer', 'Elizabeth',
        'Nicole', 'Laura', 'Sarah', 'Rachel', 'Megan', 'Amy', 'Lucy', 'Jodie',
        'Kate', 'Anna', 'Emily', 'Francesca', 'Harriet', 'Imogen', 'Jessica',
        'Rosie', 'Holly', 'Alice', 'Daisy', 'Poppy', 'Ellie', 'Freya',
        'Abigail', 'Phoebe', 'Molly', 'Amber', 'Jasmine', 'Sienna', 'Paige',
        'Ava', 'Mia', 'Isla', 'Willow', 'Violet', 'Aurora', 'Ivy', 'Ruby',
        'Lena', 'Nina', 'Maria', 'Elena', 'Sofia', 'Valentina', 'Clara',
        'Beatrice', 'Florence', 'Celeste', 'Diana', 'Elsa', 'Heidi', 'Lexi',
    ];

    private array $lastNames = [
        'Smith', 'Jones', 'Williams', 'Brown', 'Taylor', 'Davies', 'Evans',
        'Wilson', 'Thomas', 'Roberts', 'Johnson', 'Lewis', 'Walker', 'Robinson',
        'Wood', 'Thompson', 'White', 'Watson', 'Jackson', 'Wright', 'Green',
        'Harris', 'Cooper', 'King', 'Lee', 'Martin', 'Clarke', 'James',
        'Morgan', 'Hughes', 'Edwards', 'Hill', 'Moore', 'Clark', 'Harrison',
        'Scott', 'Young', 'Morris', 'Hall', 'Ward', 'Turner', 'Carter',
        'Phillips', 'Mitchell', 'Patel', 'Singh', 'Khan', 'Ahmed', 'Ali',
        'Bennett', 'Coleman', 'Dixon', 'Fletcher', 'Gray', 'Howard',
        'Murray', 'Newton', 'Owen', 'Price', 'Quinn', 'Reed', 'Shaw',
        'Stewart', 'Sullivan', 'Tucker', 'Underwood', 'Vaughan', 'Webb',
    ];

    private array $tags = [
        'VIP', 'Regular', 'Colour Client', 'Bridal', 'Teen',
        'Needs Reminder', 'Loyalty Card', 'Referral', 'High Spend',
        'Sensitive Skin', 'Expects Discount', 'Allergy Alert',
    ];

    private array $allergies = [
        null, null, null, null, null, null,   // most have none
        'PPD — patch tested 03/2024, no reaction. Re-test required if >6 months.',
        'Latex allergy — use vinyl gloves only.',
        'Sensitive to ammonia-based products — use ammonia-free colour only.',
        'Nut allergy (severe). Avoid products containing almond or macadamia oil.',
        'Sensitive to fragrance — use fragrance-free products where possible.',
    ];

    private array $sources = [
        'online_booking', 'instagram', 'google', 'walk_in', 'referral',
        'facebook', 'website', 'phone', 'whatsapp',
    ];

    private array $colors = [
        '#C4556B','#B8943A','#5A8A72','#3B82F6','#8B5CF6',
        '#D97706','#059669','#EC4899','#7C6B9E','#0EA5E9',
    ];

    private array $formulaData = [
        ['base_color' => '7N', 'highlight_color' => 'BlondorPlex',      'toner' => '8/38', 'developer' => '20vol', 'olaplex' => 'No.1 in colour, No.2 post', 'technique' => 'Foilayage', 'result_notes' => 'Beautiful beige blonde. Client very happy.'],
        ['base_color' => '6/1','highlight_color' => 'Illumina 9/43',    'toner' => 'Clear gloss', 'developer' => '12vol', 'olaplex' => 'No.2 post', 'technique' => 'Slices and weaves', 'result_notes' => 'Cool ash blonde achieved.'],
        ['base_color' => '5N', 'highlight_color' => 'Blondor Freelights','toner' => 'Shinefinity 09/73', 'developer' => '20vol', 'olaplex' => 'No.1 bond multiplier', 'technique' => 'Balayage — freehand', 'result_notes' => 'Warm caramel tones. Loved by client.'],
        ['base_color' => '4/0','highlight_color' => null,               'toner' => 'Koleston 6/1', 'developer' => '20vol', 'olaplex' => null, 'technique' => 'Root-to-tip application', 'result_notes' => 'Single process global. Clean coverage.'],
        ['base_color' => '8/0','highlight_color' => 'BlondorPlex',      'toner' => 'Koleston 10/38', 'developer' => '30vol', 'olaplex' => 'No.1 in bleach', 'technique' => 'Babylights — foils', 'result_notes' => 'Ultra-light blonde. Scalp very sensitive, avoid 30vol next visit.'],
    ];

    public function run(): void
    {
        $salon     = Salon::first();
        $staffList = Staff::where('salon_id', $salon->id)->get();
        $count     = 0;

        for ($i = 0; $i < 150; $i++) {
            $firstName   = $this->firstNames[array_rand($this->firstNames)];
            $lastName    = $this->lastNames[array_rand($this->lastNames)];
            $isVip       = $i < 20;   // First 20 are VIPs
            $visitCount  = $isVip ? rand(15, 80) : rand(0, 20);
            $totalSpent  = $visitCount * rand(55, 220);
            $lastVisit   = $visitCount > 0
                ? now()->subDays(rand(1, 180))->toDateTimeString()
                : null;
            $dob         = $i % 3 === 0
                ? now()->subYears(rand(22, 65))->subDays(rand(0, 364))->toDateString()
                : null;
            $preferredStaff = $staffList->random();
            $source      = $this->sources[array_rand($this->sources)];
            $allergy     = $this->allergies[array_rand($this->allergies)];
            $marketing   = rand(0, 10) > 2;  // ~70% consent

            $clientTags = [];
            if ($isVip) $clientTags[] = 'VIP';
            if ($visitCount > 10) $clientTags[] = 'Regular';
            if (rand(0, 5) === 0) $clientTags[] = $this->tags[array_rand($this->tags)];

            $client = Client::create([
                'salon_id'           => $salon->id,
                'first_name'         => $firstName,
                'last_name'          => $lastName,
                'email'              => strtolower($firstName . '.' . $lastName . rand(10, 99) . '@example.com'),
                'phone'              => '07' . rand(700, 999) . ' ' . rand(100000, 999999),
                'date_of_birth'      => $dob,
                'color'              => $this->colors[array_rand($this->colors)],
                'tags'               => $clientTags,
                'preferred_staff_id' => $preferredStaff->id,
                'allergies'          => $allergy,
                'marketing_consent'  => $marketing,
                'sms_consent'        => $marketing,
                'email_consent'      => true,
                'status'             => $i >= 145 ? 'inactive' : 'active',
                'is_vip'             => $isVip,
                'total_spent'        => $totalSpent,
                'visit_count'        => $visitCount,
                'last_visit_at'      => $lastVisit,
                'source'             => $source,
                'created_at'         => now()->subDays(rand(30, 730)),
            ]);

            // Add a note for ~40% of clients
            if ($i % 5 < 2) {
                $noteTypes = ['general', 'preference', 'allergy'];
                $noteContent = [
                    "general"    => "Prefers appointments on {$preferredStaff->first_name}'s days. Always likes a coffee on arrival.",
                    "preference" => "Likes conversation kept minimal. Always requests a specific playlist.",
                    "allergy"    => $allergy ?? "Check before each visit for any new sensitivities.",
                ];
                $noteType = $noteTypes[array_rand($noteTypes)];
                ClientNote::create([
                    'client_id' => $client->id,
                    'staff_id'  => $preferredStaff->id,
                    'type'      => $noteType,
                    'content'   => $noteContent[$noteType],
                    'is_pinned' => $noteType === 'allergy',
                ]);
            }

            // Add formula for colour clients (~50%)
            if ($visitCount > 2 && $i % 2 === 0) {
                $formula = $this->formulaData[array_rand($this->formulaData)];
                ClientFormula::create(array_merge($formula, [
                    'client_id'    => $client->id,
                    'staff_id'     => $preferredStaff->id,
                    'is_current'   => true,
                    'natural_level'=> (string) rand(3, 8),
                    'target_level' => (string) rand(7, 10),
                    'used_at'      => $lastVisit ? Carbon::parse($lastVisit)->toDateString() : now()->toDateString(),
                ]));
            }

            $count++;
        }

        $this->command->info("   ✓  {$count} demo clients created (20 VIP, 150 total).");
    }
}
