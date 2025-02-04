<?php

namespace App\Actions;

use App\Models\User;
use EonVisualMedia\LaravelKlaviyo\Klaviyo;

final readonly class SubscribeToKlaviyoList
{
    public function handle(User $user, $listId)
    {

        $userdata = [
            "location" => [
                "address1" => $user->address,
                "city" => $user->city,
                "country" => $user->country,
                "region" => $user->state,
                "zip" => $user->zipcode,
                "ip" => request()->ip()
            ],
            'email'         => $user->email,
            'external_id'   => $user->id,
            'phone_number'  => $user->number,
            "first_name" => $user->fullname,

        ];
        if (!empty($user->klaviyo_id)) {
            $profileResponse = Klaviyo::get("profiles/{$user->klaviyo_id}")->json();
            if (isset($profileResponse['errors'])) {
                $user->klaviyo_last_error = $profileResponse;
                $user->klaviyo_id = "";
                $user->save();
                return;
            }
        }
        if (empty($user->klaviyo_id)) {
            $profile = Klaviyo::post("profile-import", [
                'data' => [
                    'type'          => 'profile',
                    'attributes' => $userdata
                ]
            ]);
            $profileResponse = $profile->json();
            if (isset($profileResponse['errors'])) {
                $user->klaviyo_last_error = $profileResponse;
                $user->save();
                return;
            } else {
                $user->klaviyo_id = $profileResponse['data']['id'];
                $user->save();
            }
        }
        //Create user profile on Klaviyo

        // Add profile to list
        $response= Klaviyo::post("profile-subscription-bulk-create-jobs", [
            'data' => [
                'type'          => "profile-subscription-bulk-create-job",
                'attributes'    => [
                    'profiles' => [
                        'data' => [
                            [
                                'type'       => 'profile',
                               
                                'attributes' => [
                                    'email'         => $user->email,
                                    'subscriptions' => [
                                        'email' => [
                                            'marketing' => [
                                                'consent' => 'SUBSCRIBED'
                                            ]
                                        ],
                                    ],
                                ],
                                "id" => $user->klaviyo_id,
                            ]
                        ]
                    ]
                ],

                'relationships' => [
                    'list' => [
                        'data' => [
                            'type' => 'list',
                            'id'   => $listId
                        ]
                    ]
                ]
            ]
        ]);
        return $response->getBody();
    }
}
