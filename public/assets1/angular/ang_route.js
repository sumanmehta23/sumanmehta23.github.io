bgfx.config(['$routeProvider', function ($routeProvider) {
  $routeProvider.when("/dashboard", {
      templateUrl: "http://localhost/client.alphafx.com/views/dashboard.php"
    })

    .when("/", {
      templateUrl: "http://localhost/client.alphafx.com/views/dashboard.php"
    })
   
    .when("/404", {
      templateUrl: "http://localhost/client.alphafx.com/views/404.php"
    })
   

    .when("/profile", {
      templateUrl: "http://localhost/client.alphafx.com/views/profile/profile.php"
    })

    .when("/verification", {
      templateUrl: "http://localhost/client.alphafx.com/views/verification/account_verification.php"
    })
     .when("/verification_history", {
      templateUrl: "http://localhost/client.alphafx.com/views/verification/verification_history.php"
    })

   

    .when("/deposit", {
      templateUrl: "http://localhost/client.alphafx.com/views/Funds/deposit.php"
    })

    .when("/withdraw", {
      templateUrl: "http://localhost/client.alphafx.com/views/Funds/withdraw.php"
    })

    .when("/setting", {
      templateUrl: "http://localhost/client.alphafx.com/views/profile/setting.php"
    })

    .when("/live", {
      templateUrl: "http://localhost/client.alphafx.com/views/account/live.php"
    })

    .when("/demo", {
      templateUrl: "http://localhost/client.alphafx.com/views/account/demo.php"
    })

    .when("/download", {
      templateUrl: "http://localhost/client.alphafx.com/views/platform/download.php"
    })

    .when("/eBook", {
      templateUrl: "http://localhost/client.alphafx.com/views/Trading_Services/eBook.php"
    })

    .when("/economic_calendar", {
      templateUrl: "http://localhost/client.alphafx.com/views/Trading_Services/ec.php"
    })

    .when("/signals", {
      templateUrl: "http://localhost/client.alphafx.com/views/Trading_Services/trading_signals.php"
    })

    .when("/indicators", {
      templateUrl: "http://localhost/client.alphafx.com/views/Trading_Services/trading_indicators.php"
    })

    .when("/fundReport", {
      templateUrl: "http://localhost/client.alphafx.com/views/Funds/report.php"
    })

    .when("/pm/:login/:amount", {
      templateUrl: "http://localhost/client.alphafx.com/views/Funds/ePayments/pm.php"
    })

    .when("/gp/:login/:amount", {
      templateUrl: "http://localhost/client.alphafx.com/views/Funds/ePayments/global_pay.php"
    })

    .when("/bitcoinpay/:login/:amount", {
      templateUrl: "http://localhost/client.alphafx.com/views/Funds/ePayments/bitcoinpay.php"
    })

    .when("/Manager_Analysis", {
      templateUrl: "http://localhost/client.alphafx.com/views/pamm/pamm_managers.php"
    })
    
    .when("/manager_analysis", {
      templateUrl: "http://localhost/client.alphafx.com/views/pamm/pamm_invest.php"
    })

    .when("/manager_analysis/:pamm_manager_login", {
      templateUrl: "http://localhost/client.alphafx.com/views/pamm/manager_analysis.php"
    })
     .when("/live_history", {
      templateUrl: "http://localhost/client.alphafx.com/views/live_history.php"
    })
   .when("/demo_history", {
      templateUrl: "http://localhost/client.alphafx.com/views/demo_history.php"
    })
    .when("/deposit_history", {
      templateUrl: "http://localhost/client.alphafx.com/views/Funds/deposit_history.php"
    })
      .when("/withdrawal_history", {
      templateUrl: "http://localhost/client.alphafx.com/views/Funds/withdrawal_history.php"
    })
   
    .when("/success", {
      templateUrl: "http://localhost/client.alphafx.com/views/Funds/ePayments/success.php"
    })

    .when("/reject", {
      templateUrl: "http://localhost/client.alphafx.com/views/Funds/ePayments/reject.php"
    })
/*
    .otherwise({
      redirectTo: '/404'
    });
    */
}]);