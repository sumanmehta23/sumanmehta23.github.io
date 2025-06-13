<?php

// Simple script to diagnose and fix UTF-8 issues in the database
// To run it: php utf8_diagnose.php
// To fix issues: php utf8_diagnose.php --fix

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "Starting UTF-8 diagnosis...\n";

// Get table name from User model
$table = (new User())->getTable();
echo "User table: " . $table . "\n";

// Get column list
$columns = DB::getSchemaBuilder()->getColumnListing($table);
$textColumns = [];

// Filter only string columns
foreach ($columns as $column) {
    try {
        $columnType = DB::getSchemaBuilder()->getColumnType($table, $column);
        if (in_array($columnType, ['string', 'text', 'longtext', 'mediumtext'])) {
            $textColumns[] = $column;
        }
    } catch (Exception $e) {
        echo "Could not determine type for column $column: " . $e->getMessage() . "\n";
    }
}

echo "Found " . count($textColumns) . " text columns to scan: " . implode(', ', $textColumns) . "\n";

// Scan data in batches
$batchSize = 100;
$totalUsers = User::count();
$scanned = 0;
$problemUsers = [];

echo "Scanning $totalUsers users in batches of $batchSize...\n";

for ($offset = 0; $offset < $totalUsers; $offset += $batchSize) {
    $users = User::skip($offset)->take($batchSize)->get();
    echo "Scanning batch " . (floor($offset / $batchSize) + 1) . " of " . ceil($totalUsers / $batchSize) . "...\n";

    foreach ($users as $user) {
        $scanned++;
        $attributes = $user->getAttributes();
        $userHasIssue = false;
        $issues = [];

        foreach ($textColumns as $column) {
            if (!isset($attributes[$column]) || is_null($attributes[$column])) {
                continue;
            }

            $value = $attributes[$column];
            if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                $userHasIssue = true;
                $issues[] = $column;
            }
        }

        if ($userHasIssue) {
            $problemUsers[] = [
                'id' => $user->id,
                'problem_columns' => $issues
            ];

            // Display information for problematic users
            echo "Found invalid UTF-8 in user ID: " . $user->id . "\n";
            echo "Problematic columns: " . implode(', ', $issues) . "\n";

            // Output a sample of the first problematic field
            if (count($issues) > 0) {
                $firstField = $issues[0];
                $value = $user->$firstField;
                echo "Sample bytes in field '$firstField': ";
                for ($i = 0; $i < min(20, strlen($value)); $i++) {
                    echo sprintf('\\x%02x', ord($value[$i]));
                }
                echo "\n";

                // Immediately fix this user (optional)
                if (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] === '--fix') {
                    echo "Fixing user $user->id...\n";
                    foreach ($issues as $column) {
                        $originalValue = $user->$column;
                        $fixedValue = mb_convert_encoding($originalValue, 'UTF-8', 'UTF-8');

                        if ($fixedValue === '') {
                            // If conversion resulted in empty string, use a placeholder
                            $fixedValue = '[encoding-error]';
                        }

                        DB::table($table)->where('id', $user->id)->update([$column => $fixedValue]);
                        echo "Fixed column '$column' for user $user->id\n";
                    }
                }
            }
        }

        // Limit to first 50 problematic users to avoid overwhelming output
        if (count($problemUsers) >= 50 && !isset($_SERVER['argv'][1])) {
            echo "Reached limit of 50 problematic users. Use --all to scan all users.\n";
            break 2;
        }
    }
}

echo "Scan complete.\n";
echo "Found " . count($problemUsers) . " users with invalid UTF-8 data out of $scanned scanned.\n";

if (count($problemUsers) > 0) {
    echo "\nFIX INSTRUCTIONS:\n";
    echo "============================================================\n";
    echo "The following users have UTF-8 encoding issues:\n";

    $problemUserIds = array_column($problemUsers, 'id');
    echo implode(', ', array_slice($problemUserIds, 0, 20)) . (count($problemUserIds) > 20 ? "... and " . (count($problemUserIds) - 20) . " more" : "") . "\n\n";

    echo "To fix these issues, run this script with the --fix parameter:\n";
    echo "php utf8_diagnose.php --fix\n\n";

    // Generate fix SQL for the first few problematic users
    echo "Or you can manually fix them with these SQL commands:\n";
    $fixCount = min(5, count($problemUsers));

    for ($i = 0; $i < $fixCount; $i++) {
        $userId = $problemUsers[$i]['id'];
        $columns = $problemUsers[$i]['problem_columns'];

        foreach (array_slice($columns, 0, 3) as $column) {
            $user = User::find($userId);
            if ($user) {
                $value = $user->$column;
                if (is_string($value)) {
                    $fixedValue = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    echo "-- Fix for user $userId, column '$column':\n";
                    echo "UPDATE $table SET `$column` = '" . addslashes($fixedValue) . "' WHERE id = '$userId';\n";
                }
            }
        }
    }

    echo "\nNote: These fixes use mb_convert_encoding to clean invalid UTF-8 characters.\n";
    echo "This will replace invalid sequences with the Unicode replacement character (�).\n";
    echo "Caution: Always backup your database before making bulk updates!\n";
} else {
    echo "No UTF-8 encoding issues found!\n";
}
