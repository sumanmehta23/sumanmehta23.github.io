# Quick Reference: Sync Live MT5 Accounts Trades Command

## What Was Created

1. **Migration**: `database/migrations/2026_04_01_000000_add_last_trade_sync_at_to_accounts_table.php`
   - Adds `last_trade_sync_at` timestamp column to accounts table
   - Status: ✅ Already migrated

2. **Command**: `app/Console/Commands/SyncLiveAccountsTrades.php`
   - Main command for syncing live MT5 account trades
   - Handles MT5 API error code 13 (MT_RET_ERR_NOTFOUND)
   - Updates `last_trade_sync_at` after successful sync
   - Status: ✅ Ready to use

3. **Documentation**: `docs/SYNC_LIVE_ACCOUNTS_TRADES_COMMAND.md`
   - Comprehensive guide with examples
   - Troubleshooting section
   - Architecture overview

## Key Features

✅ Marks accounts as `not_found_in_mt5` when error code 13 is returned
✅ Updates `last_trade_sync_at` timestamp after successful sync
✅ Handles pagination for large trade histories (max 100 per page)
✅ Groups trades by PositionID to identify open/closed positions
✅ Comprehensive error logging and reporting
✅ Selective account syncing with --account-code option
✅ Customizable date range for trade history

## Quick Start

### Sync all live accounts (default: up to 100)
```bash
php artisan app:sync-live-accounts-trades
```

### Sync a specific account
```bash
php artisan app:sync-live-accounts-trades --account-code=123456
```

### Sync and mark accounts not found
```bash
php artisan app:sync-live-accounts-trades --mark-not-found --limit=50
```

### Sync with custom date range
```bash
php artisan app:sync-live-accounts-trades --from="January 01,2025" --to="December 31,2025"
```

## How It Works

1. **Fetches accounts** from database (demo=false, account_request_status=1)
2. **Gets trade total** from MT5 API using HistoryGetTotal()
3. **Handles not-found error**: If error code 13 returned:
   - Logs warning
   - Marks account with `not_found_in_mt5 = true` if --mark-not-found flag used
   - Sets `deletion_type = 'not_found_in_mt5'`
4. **Fetches trades in pages**: Uses HistoryGetPage() to get max 100 trades per call
5. **Groups trades**: Groups by PositionID to match open/close pairs
6. **Processes trades**: 
   - Opens: Single order (status='open')
   - Closed: 2+ orders (status='closed')
7. **Updates database**: Upserts trades using account_id + position_id as key
8. **Updates timestamp**: Sets `last_trade_sync_at = now()` on success

## Error Handling

| Error Code | Meaning | Action |
|---------|---------|--------|
| 0 | Success | Continue |
| 13 | Not found in MT5 | Mark account if --mark-not-found used |
| 7 | Network error | Log and continue to next account |
| 9 | Timeout | Log and continue to next account |
| Other | API error | Log error code and continue |

## Output Example

```
Starting live MT5 accounts trades sync...
Found 5 account(s) to sync.
Syncing trades for account: 794195
Found 25 trades for account: 794195
✓ Successfully synced account: 794195
...
==================================================
Sync Summary:
Successfully synced: 45
Not found in MT5: 2
Failed: 3
==================================================
```

## Command Reference

All options:
- `--account-code=CODE` - Sync specific account
- `--limit=N` - Max accounts to sync (default: 100)
- `--from=DATE` - Start date (default: September 01,2024)
- `--to=DATE` - End date (default: March 31,2080)
- `--mark-not-found` - Mark not-found accounts with flag

Global options:
- `-v` or `--verbose` - Show detailed output
- `-q` or `--quiet` - Suppress output
- `--env=ENVIRONMENT` - Environment to run in
