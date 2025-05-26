# Price Snapshots Tool

This tool provides commands for updating price data for trading symbols in the `price_snapshots` table.

## Available Commands

### Update Price Snapshots

```bash
php artisan app:update-price-snapshots [options]
```

This command fetches current price data from external APIs and updates the `price_snapshots` table.

#### Options:

- `--source=api_name` - Specify the data source (default: alphavantage)
  - Available sources: `alphavantage`, `coingecko`, `exchangerate`
- `--symbols=SYM1,SYM2` - Comma-separated list of symbols to update (default: predefined list)
- `--key=your_api_key` - API key for Alpha Vantage (can also be set in .env file)

#### Examples:

```bash
# Update using default symbols from Alpha Vantage
php artisan app:update-price-snapshots --key=YOUR_ALPHAVANTAGE_API_KEY

# Update specific symbols using CoinGecko (free, no API key needed)
php artisan app:update-price-snapshots --source=coingecko --symbols=BTCUSD,ETHUSD

# Update forex pairs using Exchange Rate API (free, no API key needed)
php artisan app:update-price-snapshots --source=exchangerate --symbols=EURUSD,GBPUSD,USDJPY
```

## API Information

### Alpha Vantage
- Offers forex and crypto data
- Free tier: 5 API calls per minute, 500 per day
- Get API key: [Alpha Vantage](https://www.alphavantage.co/support/#api-key)

### CoinGecko
- Offers cryptocurrency data
- Free tier: 10-50 calls per minute
- No API key required for basic use

### Exchange Rate API
- Offers forex exchange rates
- Free tier: 1,000 API calls per month
- No API key required for basic use

## Configuration

Add your Alpha Vantage API key to your `.env` file:

```
ALPHAVANTAGE_API_KEY=your_api_key_here
```

## Scheduling

The command is scheduled to run hourly. To use the Laravel scheduler, make sure you have a cron job set up:

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```
