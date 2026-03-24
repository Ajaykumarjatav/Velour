<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Client;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\MarketingCampaign;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\Review;
use App\Models\Salon;
use App\Models\SalonNotification;
use App\Models\SalonSetting;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// ════════════════════════════════════════════════════════════════════════════
// ReviewSeeder
// ════════════════════════════════════════════════════════════════════════════
class ReviewSeeder extends Seeder
{
    private array $comments = [
        5 => [
            "Absolutely exceptional service. Isabelle is a true artist — my balayage looks incredible and lasted beautifully. Won't go anywhere else.",
            "Maison Lumière is in a league of its own. The attention to detail, the ambience, the results — all 5 stars. Thank you so much!",
            "Best salon experience I've had in years. Margot is so talented and really listens to what you want. I left feeling brand new.",
            "My hair has never looked this good. The whole team is wonderful, professional, and so welcoming. Book here, you won't regret it.",
            "Camille transformed my skin in one session. I've never received so many compliments. Truly luxury in every sense.",
            "Léa did my colour and it's exactly what I envisioned — warm, dimensional, stunning. Highly recommend!",
            "From the warm welcome to the quality of the treatment, everything was perfect. My lash lift and brow lamination look amazing.",
        ],
        4 => [
            "Really lovely salon, very professional staff and great results. Took slightly longer than expected but the finish was worth it.",
            "Beautiful environment and skilled team. I had a full balayage and the result was gorgeous. Would definitely return.",
            "Excellent service from start to finish. Loved the consultation and the care taken with my colour. Very impressed.",
            "Great experience overall. Margot was attentive and did a brilliant job on my nails. Minor wait at the start but nothing major.",
        ],
        3 => [
            "Nice salon and friendly team. My blowdry was good but felt a little rushed at the end. Would still return and try again.",
            "Lovely atmosphere. Results were good but not quite what I'd asked for — perhaps I wasn't clear enough. Staff were very helpful.",
        ],
    ];

    public function run(): void
    {
        $salon   = Salon::first();
        $clients = Client::where('salon_id', $salon->id)->where('status','active')->take(60)->get();
        $staff   = Staff::where('salon_id', $salon->id)->get();
        $count   = 0;

        // Distribution: ~60% five-star, 30% four-star, 10% three-star
        $ratings = array_merge(
            array_fill(0, 36, 5),
            array_fill(0, 18, 4),
            array_fill(0, 6,  3),
        );
        shuffle($ratings);

        foreach ($clients->take(count($ratings)) as $i => $client) {
            $rating  = $ratings[$i];
            $comment = $this->comments[$rating][array_rand($this->comments[$rating])];
            $member  = $staff->random();
            $reply   = $rating >= 4 ? $this->ownerReply($rating, $client->first_name) : null;

            Review::create([
                'salon_id'      => $salon->id,
                'client_id'     => $client->id,
                'staff_id'      => $member->id,
                'rating'        => $rating,
                'comment'       => $comment,
                'source'        => ['velour','google','google','facebook'][array_rand([0,1,2,3])],
                'reviewer_name' => $client->first_name . ' ' . substr($client->last_name, 0, 1) . '.',
                'owner_reply'   => $reply,
                'replied_at'    => $reply ? now()->subDays(rand(1, 5)) : null,
                'is_public'     => true,
                'is_verified'   => true,
                'created_at'    => now()->subDays(rand(1, 90)),
            ]);

            $count++;
        }

        $this->command->info("   ✓  {$count} reviews created.");
    }

    private function ownerReply(int $rating, string $name): ?string
    {
        if ($rating < 4) return null;
        $replies = [
            "Thank you so much, {$name}! It means the world to us that you had such a wonderful experience. We can't wait to see you again soon! 💛",
            "What a lovely review, {$name} — thank you! It's always such a pleasure looking after you. See you next time! ✨",
            "We're absolutely thrilled to hear this, {$name}! Your kind words have made the team's day. Thank you for choosing Maison Lumière.",
        ];
        return $replies[array_rand($replies)];
    }
}
