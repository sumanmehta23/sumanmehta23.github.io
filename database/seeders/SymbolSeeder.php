<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SymbolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $symbols = json_decode(File::get(database_path('seeders/data/symbols.json')), true);
// dd($symbols);
$symbols = array_map(function($symbol) {
    return array_change_key_case($symbol, CASE_LOWER);
}, $symbols);
         // Insert each record into the 'leverage' table
         foreach ($symbols as $symbol) {
            DB::table('symbols')->insert([
                'symbol' => $symbol['symbol'],
                'path' => $symbol['path'],
                'isin' => $symbol['isin'] ?? '',
                'description' => $symbol['description'] ?? '',
                'international' => $symbol['international'] ?? '',
                'basis' => $symbol['basis'] ?? '',
                'source' => $symbol['source'] ?? '',
                'page' => $symbol['page'] ?? '',
                'currency_base' => $symbol['currency_base'] ?? 'USD',
                'currency_base_digits' => $symbol['currency_base_digits'] ?? 2,
                'currency_profit' => $symbol['currency_profit'] ?? 'USD',
                'currency_profit_digits' => $symbol['currency_profit_digits'] ?? 2,
                'currency_margin' => $symbol['currency_margin'] ?? 'USD',
                'currency_margin_digits' => $symbol['currency_margin_digits'] ?? 2,
                'color' => $symbol['color'] ?? 4278190080,
                'color_background' => $symbol['color_background'] ?? 4278190080,
                'digits' => $symbol['digits'] ?? 2,
                'point' => $symbol['point'] ?? 0.01,
                'multiply' => $symbol['multiply'] ?? 100,
                'tick_flags' => $symbol['tick_flags'] ?? 1,
                'tick_book_depth' => $symbol['tick_book_depth'] ?? 0,
                'chart_mode' => $symbol['chart_mode'] ?? 0,
                'filter_soft' => $symbol['filter_soft'] ?? 0,
                'filter_soft_ticks' => $symbol['filter_soft_ticks'] ?? 10,
                'filter_hard' => $symbol['filter_hard'] ?? 0,
                'filter_hard_ticks' => $symbol['filter_hard_ticks'] ?? 10,
                'filter_discard' => $symbol['filter_discard'] ?? 0,
                'filter_spread_max' => $symbol['filter_spread_max'] ?? 0,
                'filter_spread_min' => $symbol['filter_spread_min'] ?? 1,
                'filter_gap' => $symbol['filter_gap'] ?? 0,
                'filter_gap_ticks' => $symbol['filter_gap_ticks'] ?? 0,
                'trade_mode' => $symbol['trade_mode'] ?? 4,
                'trade_flags' => $symbol['trade_flags'] ?? 2,
                'calc_mode' => $symbol['calc_mode'] ?? 4,
                'exec_mode' => $symbol['exec_mode'] ?? 2,
                'gtc_mode' => $symbol['gtc_mode'] ?? 0,
                'fill_flags' => $symbol['fill_flags'] ?? 2,
                'expir_flags' => $symbol['expir_flags'] ?? 15,
                'order_flags' => $symbol['order_flags'] ?? 127,
                'spread' => $symbol['spread'] ?? 0,
                'spread_balance' => $symbol['spread_balance'] ?? 0,
                'spread_diff' => $symbol['spread_diff'] ?? 0,
                'spread_diff_balance' => $symbol['spread_diff_balance'] ?? 0,
                'tick_value' => $symbol['tick_value'] ?? 0,
                'tick_size' => $symbol['tick_size'] ?? 0,
                'contract_size' => $symbol['contract_size'] ?? 1,
                'stops_level' => $symbol['stops_level'] ?? 0,
                'freeze_level' => $symbol['freeze_level'] ?? 0,
                'quotes_timeout' => $symbol['quotes_timeout'] ?? 300,
                'volume_min' => $symbol['volume_min'] ?? 1000,
                'volume_min_ext' => $symbol['volume_min_ext'] ?? 10000000,
                'volume_max' => $symbol['volume_max'] ?? 10000000,
                'volume_max_ext' => $symbol['volume_max_ext'] ?? 100000000000,
                'volume_step' => $symbol['volume_step'] ?? 1000,
                'volume_step_ext' => $symbol['volume_step_ext'] ?? 10000000,
                'volume_limit' => $symbol['volume_limit'] ?? 0,
                'volume_limit_ext' => $symbol['volume_limit_ext'] ?? 0,
                'margin_flags' => $symbol['margin_flags'] ?? 0,
                'margin_initial' => $symbol['margin_initial'] ?? 0,
                'margin_maintenance' => $symbol['margin_maintenance'] ?? 0,
                'margin_rate_initial' => $symbol['margin_rate_initial'] ?? json_encode([]),
                'margin_rate_maintenance' => $symbol['margin_rate_maintenance'] ?? json_encode([]),
                'margin_rate_liquidity' => $symbol['margin_rate_liquidity'] ?? 0,
                'margin_hedged' => $symbol['margin_hedged'] ?? 5,
                'margin_rate_currency' => $symbol['margin_rate_currency'] ?? 0,
                'margin_long' => $symbol['margin_long'] ?? 0.01000000,
                'margin_short' => $symbol['margin_short'] ?? 0.01000000,
                'margin_limit' => $symbol['margin_limit'] ?? 0,
                'margin_stop' => $symbol['margin_stop'] ?? 0,
                'margin_stop_limit' => $symbol['margin_stop_limit'] ?? 0,
                'swap_mode' => $symbol['swap_mode'] ?? 1,
                'swap_long' => $symbol['swap_long'] ?? -1.638,
                'swap_short' => $symbol['swap_short'] ?? 0.427,
                'swap_3_day' => $symbol['swap_3_day'] ?? 3,
                'time_start' => $symbol['time_start'] ?? 0,
                'time_expiration' => $symbol['time_expiration'] ?? 0,
                'sessions_quotes' => $symbol['sessions_quotes'] ?? json_encode([]),
                'sessions_trades' => $symbol['sessions_trades'] ?? json_encode([]),
                're_flags' => $symbol['re_flags'] ?? 0,
                're_timeout' => $symbol['re_timeout'] ?? 7,
                'ie_check_mode' => $symbol['ie_check_mode'] ?? 0,
                'ie_timeout' => $symbol['ie_timeout'] ?? 7,
                'ie_slip_profit' => $symbol['ie_slip_profit'] ?? 2,
                'ie_slip_losing' => $symbol['ie_slip_losing'] ?? 2,
                'ie_volume_max' => $symbol['ie_volume_max'] ?? 0,
                'ie_volume_max_ext' => $symbol['ie_volume_max_ext'] ?? 0,
                'price_settle' => $symbol['price_settle'] ?? 0,
                'price_limit_max' => $symbol['price_limit_max'] ?? 0,
                'price_limit_min' => $symbol['price_limit_min'] ?? 0,
                'price_strike' => $symbol['price_strike'] ?? 0,
                'options_mode' => $symbol['options_mode'] ?? 0,
                'face_value' => $symbol['face_value'] ?? 0,
                'accrued_interest' => $symbol['accrued_interest'] ?? 0,
                'splice_type' => $symbol['splice_type'] ?? 0,
                'splice_time_type' => $symbol['splice_time_type'] ?? 0,
                'splice_time_days' => $symbol['splice_time_days'] ?? 0,
            ]);
         }
    }
}
