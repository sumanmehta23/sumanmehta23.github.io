<?php

namespace Database\Seeders;

use App\Models\LearnSection;
use App\Models\LearnVideo;
use Illuminate\Database\Seeder;

class LearnContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                'title' => 'Getting Started',
                'slug' => 'getting-started',
                'sort_order' => 1,
                'is_active' => true,
                'videos' => [
                    ['title' => 'Welcome to the Platform', 'wistia_id' => 'abc123def4', 'tags' => ['intro', 'platform'], 'sort_order' => 1, 'is_active' => true],
                    ['title' => 'How to Verify Your Account', 'wistia_id' => 'ghi567jkl8', 'tags' => ['kyc', 'account'], 'sort_order' => 2, 'is_active' => true],
                    ['title' => 'Profile and Security Basics', 'wistia_id' => 'mno901pqr2', 'tags' => ['profile', 'security'], 'sort_order' => 3, 'is_active' => true],
                ],
            ],
            [
                'title' => 'Funding and Withdrawals',
                'slug' => 'funding-and-withdrawals',
                'sort_order' => 2,
                'is_active' => true,
                'videos' => [
                    ['title' => 'How to Deposit Funds', 'wistia_id' => 'stu345vwx6', 'tags' => ['deposit', 'wallet'], 'sort_order' => 1, 'is_active' => true],
                    ['title' => 'How to Withdraw Funds', 'wistia_id' => 'yz123abcd9', 'tags' => ['withdrawal', 'wallet'], 'sort_order' => 2, 'is_active' => true],
                    ['title' => 'Transaction History Explained', 'wistia_id' => 'efg456hij7', 'tags' => ['transactions'], 'sort_order' => 3, 'is_active' => true],
                ],
            ],
            [
                'title' => 'Trading Account Management',
                'slug' => 'trading-account-management',
                'sort_order' => 3,
                'is_active' => true,
                'videos' => [
                    ['title' => 'Open a Live Account', 'wistia_id' => 'klm789nop1', 'tags' => ['live account'], 'sort_order' => 1, 'is_active' => true],
                    ['title' => 'Open a Demo Account', 'wistia_id' => 'qrs234tuv5', 'tags' => ['demo account'], 'sort_order' => 2, 'is_active' => true],
                    ['title' => 'Account Settings and Leverage', 'wistia_id' => 'wxy678zab3', 'tags' => ['leverage', 'settings'], 'sort_order' => 3, 'is_active' => true],
                ],
            ],
        ];

        foreach ($sections as $sectionData) {
            $videos = $sectionData['videos'];
            unset($sectionData['videos']);

            $section = LearnSection::updateOrCreate(
                ['slug' => $sectionData['slug']],
                $sectionData
            );

            foreach ($videos as $videoData) {
                LearnVideo::updateOrCreate(
                    [
                        'learn_section_id' => $section->id,
                        'title' => $videoData['title'],
                    ],
                    [
                        'wistia_id' => $videoData['wistia_id'],
                        'tags' => $videoData['tags'],
                        'sort_order' => $videoData['sort_order'],
                        'is_active' => $videoData['is_active'],
                    ]
                );
            }
        }
    }
}

