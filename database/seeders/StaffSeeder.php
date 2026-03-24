<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\Salon;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $salon = Salon::first();

        $members = [
            [
                'first_name'      => 'Isabelle',
                'last_name'       => 'Fontaine',
                'email'           => 'isabelle@maisondemo.app',
                'phone'           => '07700 900 001',
                'role'            => 'Senior Colourist',
                'bio'             => 'Isabelle is our lead colour specialist with over 12 years of experience. She trained in Paris and specialises in balayage and complex colour correction.',
                'specialisms'     => ['Balayage', 'Colour Correction', 'Highlights', 'Ombré', 'Toning'],
                'commission_rate' => 45.00,
                'access_level'    => 'senior',
                'color'           => '#C4556B',
                'initials'        => 'IF',
                'working_days'    => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                'start_time'      => '09:00:00',
                'end_time'        => '18:00:00',
                'hired_at'        => '2021-03-15',
                'bookable_online' => true,
                'sort_order'      => 1,
            ],
            [
                'first_name'      => 'Margot',
                'last_name'       => 'Beaumont',
                'email'           => 'margot@maisondemo.app',
                'phone'           => '07700 900 002',
                'role'            => 'Hair Stylist & Nail Artist',
                'bio'             => 'Margot brings a creative flair to every look. She excels in precision cuts and nail artistry, with a certificate from the London School of Beauty.',
                'specialisms'     => ['Precision Cuts', 'Blowdrys', 'Gel Nails', 'Nail Art', 'Bridal Styling'],
                'commission_rate' => 40.00,
                'access_level'    => 'staff',
                'color'           => '#B8943A',
                'initials'        => 'MB',
                'working_days'    => ['Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                'start_time'      => '10:00:00',
                'end_time'        => '19:00:00',
                'hired_at'        => '2022-06-01',
                'bookable_online' => true,
                'sort_order'      => 2,
            ],
            [
                'first_name'      => 'Camille',
                'last_name'       => 'Lacroix',
                'email'           => 'camille@maisondemo.app',
                'phone'           => '07700 900 003',
                'role'            => 'Skin Therapist',
                'bio'             => 'Camille is our skin health expert, trained in clinical facials and advanced skincare treatments. She offers bespoke consultations for all skin types.',
                'specialisms'     => ['Facials', 'Microdermabrasion', 'Chemical Peels', 'Brow Shaping', 'Lash Lifts'],
                'commission_rate' => 42.00,
                'access_level'    => 'staff',
                'color'           => '#5A8A72',
                'initials'        => 'CL',
                'working_days'    => ['Mon', 'Wed', 'Thu', 'Fri', 'Sat'],
                'start_time'      => '09:30:00',
                'end_time'        => '17:30:00',
                'hired_at'        => '2022-09-12',
                'bookable_online' => true,
                'sort_order'      => 3,
            ],
            [
                'first_name'      => 'Léa',
                'last_name'       => 'Moreau',
                'email'           => 'lea@maisondemo.app',
                'phone'           => '07700 900 004',
                'role'            => 'Colour Technician',
                'bio'             => 'Léa specialises in natural and ammonia-free colour treatments. She has a growing following for her signature lived-in colour blends.',
                'specialisms'     => ['Root Tint', 'Global Colour', 'Toning', 'Babylights', 'Men\'s Colour'],
                'commission_rate' => 38.00,
                'access_level'    => 'staff',
                'color'           => '#7C6B9E',
                'initials'        => 'LM',
                'working_days'    => ['Mon', 'Tue', 'Thu', 'Fri', 'Sat'],
                'start_time'      => '09:00:00',
                'end_time'        => '17:00:00',
                'hired_at'        => '2023-01-09',
                'bookable_online' => true,
                'sort_order'      => 4,
            ],
            [
                'first_name'      => 'Élise',
                'last_name'       => 'Renard',
                'email'           => 'elise@maisondemo.app',
                'phone'           => '07700 900 005',
                'role'            => 'Massage & Wellness',
                'bio'             => 'Élise is our wellness specialist, offering therapeutic massage, body treatments, and relaxation rituals. She holds an ITEC Level 3 diploma.',
                'specialisms'     => ['Swedish Massage', 'Deep Tissue', 'Hot Stone', 'Body Wraps', 'Aromatherapy'],
                'commission_rate' => 40.00,
                'access_level'    => 'staff',
                'color'           => '#D97706',
                'initials'        => 'ER',
                'working_days'    => ['Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'start_time'      => '10:00:00',
                'end_time'        => '18:00:00',
                'hired_at'        => '2023-04-17',
                'bookable_online' => true,
                'sort_order'      => 5,
            ],
            [
                'first_name'      => 'Nathalie',
                'last_name'       => 'Dubois',
                'email'           => 'nathalie@maisondemo.app',
                'phone'           => '07700 900 006',
                'role'            => 'Salon Manager',
                'bio'             => 'Nathalie oversees day-to-day operations and client experience. She also offers express services and assists with colour processing.',
                'specialisms'     => ['Client Relations', 'Blowdrys', 'Treatments', 'Express Services'],
                'commission_rate' => 35.00,
                'access_level'    => 'manager',
                'color'           => '#059669',
                'initials'        => 'ND',
                'working_days'    => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                'start_time'      => '08:30:00',
                'end_time'        => '17:30:00',
                'hired_at'        => '2020-11-01',
                'bookable_online' => false,
                'sort_order'      => 0,
            ],
        ];

        foreach ($members as $member) {
            Staff::create(array_merge($member, ['salon_id' => $salon->id]));
        }

        $this->command->info('   ✓  6 staff members created.');
    }
}
