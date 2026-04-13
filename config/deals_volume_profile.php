<?php

return [
    /**
     * Deal volume profile configuration
     * 
     * Groups accounts by pending_deal_count into brackets.
     * Accounts in the same bracket are synced together in the same job
     * to balance load and prevent timeouts.
     */
    'deals_volume_profile' => [
        // Volume brackets: [max_deals_in_bracket => batch_size_for_this_bracket]
        // Accounts with pending_deal_count <= max_deals get batch_size batched together
        'brackets' => [
            5000 => 20,        // 0-5k deals: batch 20 accounts together
            20000 => 10,       // 5k-20k deals: batch 10 accounts together
            100000 => 5,       // 20k-100k deals: batch 5 accounts together
            200000 => 2,       // 100k-200k deals: batch 2 accounts together
            PHP_INT_MAX => 1,  // 200k+ deals: individual sync (1 per job)
        ],

        /**
         * Fallback batch size if pending_deal_count is not available
         * (for accounts that haven't been counted yet)
         */
        'fallback_batch_size' => 5,

        /**
         * Always use individual (batch_size=1) for ever-synced accounts with this many+ deals
         * (high-volume incremental accounts should not be grouped)
         */
        'min_deals_for_individual_sync' => 50000,

        /**
         * Maximum accounts to process per SyncDealsJob
         * (hard limit regardless of volume bracket)
         */
        'max_accounts_per_job' => 20,
    ],
];
