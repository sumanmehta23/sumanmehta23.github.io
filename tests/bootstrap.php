<?php

/*
|--------------------------------------------------------------------------
| Test Bootstrap
|--------------------------------------------------------------------------
|
| This file is executed before running tests. It sets up the testing
| environment with proper configuration for the MySQL test database
| and mocks external services to prevent real connections.
|
*/

require_once __DIR__ . '/../vendor/autoload.php';

// Ensure we're in testing mode with MySQL test database
$_ENV['APP_ENV'] = 'testing';
$_ENV['DB_CONNECTION'] = 'mysql';
$_ENV['DB_HOST'] = '127.0.0.1';
$_ENV['DB_PORT'] = '3306';
$_ENV['DB_DATABASE'] = 'lqhcore_dev2_test';
$_ENV['DB_USERNAME'] = 'root';
$_ENV['DB_PASSWORD'] = '';
$_ENV['DB_SOCKET'] = '/tmp/mysql_3306.sock';

// Mock external service configurations to prevent real connections during testing
$_ENV['MT5_SERVER_IP'] = '127.0.0.1';
$_ENV['MT5_SERVER_PORT'] = '443';
$_ENV['MT5_SERVER_WEB_LOGIN'] = 'test_login';
$_ENV['MT5_SERVER_WEB_PASSWORD'] = 'test_password';
$_ENV['X9_API_URL'] = 'https://test.x9.com';
$_ENV['X9_API_KEY'] = 'test_api_key';
$_ENV['BREVO_API_KEY'] = 'test_brevo_api_key';

// Mock the settings function early to prevent service initialization issues
// This returns test values to avoid hitting production settings during bootstrap
if (!function_exists('settings')) {
    function settings()
    {
        return [
            'mt5_server_ip' => $_ENV['MT5_SERVER_IP'] ?? '127.0.0.1',
            'mt5_server_port' => $_ENV['MT5_SERVER_PORT'] ?? 443,
            'mt5_server_web_login' => $_ENV['MT5_SERVER_WEB_LOGIN'] ?? 'test_login',
            'mt5_server_web_password' => $_ENV['MT5_SERVER_WEB_PASSWORD'] ?? 'test_password',
            'x9_api_url' => $_ENV['X9_API_URL'] ?? 'https://test.x9.com',
            'x9_api_key' => $_ENV['X9_API_KEY'] ?? 'test_api_key',
            'api_key' => $_ENV['BREVO_API_KEY'] ?? 'test_brevo_api_key',
        ];
    }
}
