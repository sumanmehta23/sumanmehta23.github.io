<style>
    .tv-ticker-item-tape__inner-wrapper {
        align-items: flex-start !important;
        flex-direction: column !important;
        justify-content: space-between !important;
    }

    .tv-ticker-item-tape__symbol {
        display: flex !important;
        align-items: center !important;
        margin-right: 10px !important;
    }

    .tv-ticker-item-tape__last-wrapper {
        display: flex !important;
        align-items: center !important;
        margin-left: auto !important;
    }

    .tv-ticker-item-tape__change-wrapper {
        display: flex !important;
        align-items: center !important;
        margin-left: 10px !important;
    }
</style>

@php
$theme = request()->cookie('sitetheme', 'light'); // Default to 'light' if cookie not set

if ($theme === 'false') {
    $theme = 'dark'; // Change theme if cookie value is 'false'
}
@endphp

<div class="tradingview-widget-container mb-3">
    <div class="tradingview-widget-container__widget"></div>
    <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-ticker-tape.js"
      async>
      {
        "symbols": [{
          "proName": "FX_IDC:EURUSD",
          "title": "EUR to USD"
        }, {
          "proName": "BITSTAMP:BTCUSD",
          "title": "Bitcoin"
        }, {
          "proName": "BITSTAMP:ETHUSD",
          "title": "Ethereum"
        }, {
          "proName": "FX:GBPUSD",
          "description": "GBP to USD"
        }, {
          "proName": "FX:USDJPY",
          "description": "USD to JPY"
        }, {
          "proName": "FX:AUDUSD",
          "description": "AUD to USD"
        }, {
          "proName": "OANDA:USDJPY",
          "description": "USD to JPY"
        }, {
          "proName": "OANDA:USDCAD",
          "description": "USD to CAD"
        }, {
          "proName": "FX:NZDUSD",
          "description": "NZD to USD"
        }, {
          "proName": "VELOCITY:GOLD",
          "description": "Gold"
        }],
        "showSymbolLogo": true,
        "isTransparent": false,
        "displayMode": "compact",
        "colorTheme": "light",
        "width": "100%",
        "height": 74,
        "utm_medium": "widget",
        "utm_campaign": "ticker-tape"
      }
    </script>
