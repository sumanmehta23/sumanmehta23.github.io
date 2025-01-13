<?php

namespace App\Console\Commands;

use App\Models\User;
use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use League\Csv\Reader;
use App\Models\Country;
use App\Models\AccountType;
use App\Services\MT5Service;
use App\Services\MailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class ImportClientsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-clients-command {file : The path to the CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse a CSV file and output its content';
    protected $api;
    protected $mailService;
    protected $mt5Service;
    // public function __construct(MailService $mailService, MT5Service $mt5Service, MTWebAPI $api)
    // {
    //     $this->mt5Service = $mt5Service;
    //     $this->mt5Service->connect();
    //     $this->api = $this->mt5Service->getApi();
    //     $this->mailService = $mailService;
    //     // $this->api = $api;

    // }
    /**
     * Execute the console command.
     */
    public function handle(MailService $mailService, MT5Service $mt5Service, MTWebAPI $api)
    {
        ini_set("memory_limit", "-1");
        ini_set('max_execution_time', 0);
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
        $this->mailService = $mailService;
        // $this->api = $api;
        $filePath = $this->argument('file');
        $newcustomers=$existingcounter=$missingcountries=0;

        // Validate the file
        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->error("The file {$filePath} does not exist or is not readable.");
            return Command::FAILURE;
        }

        try {
            // Read the CSV file
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0); // The first row will be treated as the header

            $records = $csv->getRecords();

            foreach ($records as $index => $record) {

                $country=Country::where('country_name',$record['Where are you from?'])->first();
                if(!$country){
                    $missingcountries++;
                    Log::error("Row {$record['Where are you from?']}: Country not found");
                    // continue;
                }
                // dd($country);
                $phone=str_replace("'","",$record['Your phone number']);
                // $this->info("Row {$index}:");
                // $this->line("Full Name: " . $record['Full Name']);
                // $this->line("Email: " . $record['Your email address']);
                // $this->line("Phone: " . $phone);
                // $this->line("Location: " . $record['Where are you from?']);
                // $this->line("Request: " . $record['What do you want?']);
                // $this->line('---');
                $existing=User::where('email',$record['Your email address'])->first();
                if($existing){
                    $existingcounter++;
                    $existing->email_confirmed=1;
                    $existing->status=1;
                    if(empty( $existing->ib1) || $existing->ib1=='noIB'){
                        $existing->ib1='Swingtradinglab';
                        
                        // $accountcount=$existing->accounts()->count();
                        // foreach($existing->accounts as $account){
                        //     // dump($account->accountType);
                        //    $groupCode = str_replace("DF","ALEX",$account->accountType->ac_group);
                        //     $group = AccountType::where('ac_group', $groupCode)->first();
                        //     if($group){
                        //         $_POST["options"] =$group->id;
                        //         $account_type_id = $group->id;
                        //         $code=$account->code;
                        //         // dump($account_type_id);
                        //         if (($error_code = $this->api->UserGet($code, $trade_user)) != MTRetCode::MT_RET_OK) {
                        //             // return response()->json([
                        //             //     'status' => 'warning',
                        //             //     'message' => 'Something went wrong on Updating details',
                        //             //     'error' => MTRetCode::GetError($error_code)
                        //             // ], 400);
                        //             Log::error( 'Something went wrong on Updating details '.$code." " . MTRetCode::GetError($error_code));
                        //         }
                        //         // dd($trade_user);
                        //         // // Fetch account type details
                        //         $trade_user->Group = $group->ac_group;
                    
                        //         // Update user data via API
                        //         $updated_user = "";
                        //         if (($error_code = $this->api->UserUpdate($trade_user, $updated_user)) != MTRetCode::MT_RET_OK) {
                        //             Log::error( "Something went wrong on Updating details $code " . MTRetCode::GetError($error_code));
                        //         } else {
                        //             $account->account_type_id = $account_type_id;
                        //             $account->save();
                                    
                        //         }
                        //     }
                        // }
                        // Log::info("Row {$record['Your email address']}: User already exists and has $accountcount accounts");
                        
                        // $this->sendWelcomeToExistingUser($existing);
                    }else{
                        Log::error("Row {$record['Your email address']}: User already exists and has an IB1");
                    }
                    $existing->save();
                    continue;
                }else{
                    $user=User::create([
                        'fullname'=>$record['Full Name'],
                        'email'=>$record['Your email address'],
                        'username'=>$record['Your email address'],
                        'password'=>Hash::make('password'.rand(999,999999)),
                        'country_code'=>$country?$country->country_code:'',
                        'number'=>$phone,
                        'country'=>$record['Where are you from?'],
                        // 'request'=>$record['What do you want?'],
                        'wallet_enabled'=>1,
                        'ib1'=>'Swingtradinglab',
                        'email_confirmed'=>1,
                        'status'=>1,
                    ]);
                    if($user){
                        Log::info("Row {$record['Your email address']}: User created successfully");
                        // $this->sendWelcomeEmail($user);
                        $newcustomers++;
                    }else{
                        Log::error("Row {$record['Your email address']}: User not created");
                    }
                }
                // die();
            }
        } catch (\Exception $e) {
            $this->error('An error occurred while parsing the CSV file: ' . $e->getMessage());
            return Command::FAILURE;
        }

        Log::info('CSV parsing completed successfully. Ignored '.$missingcountries.' rows with missing countries. '.$existingcounter.' existing users found. '.$newcustomers.' new users created.');
        return Command::SUCCESS;
    }
    private function sendWelcomeEmail($user)
    {


        $email=$user->email;
        // $code =Str::random(60);
        // User::where('email', $email)->update(['emailToken' => $code]);
        $settings = settings();
        $from = $settings['email_from_address'];
        $emailSubject = 'Redeem Your $300 SwingTradingLabs Bonus on LQHMarkets';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
        $content =
            
            '<div>Thank you for choosing LQH Markets. To activate your SwingTradingLabs $300 bonus, please complete the following steps in order:</div>
            <ol>
                <li><b>Reset your password</b> using the secure link provided:<a href="'.$settings['copyright_site_name_text'] . "/forgot-password".'">'.$settings['copyright_site_name_text'] . "/forgot-password".'</a></li>
                <li>Complete the <b>Know Your Customer (KYC)</b> verification process</li>
                <li>Set up your <b>MetaTrader 5 (MT5)</b> trading account</li>
                <li>Fill out your <b>bonus request form</b> here: <ahref="https://forms.gle/Jk9SH1sxM4fEDNre6">https://forms.gle/Jk9SH1sxM4fEDNre6</a></li>
            </ol>
            <p><b>Please note:</b>  You must complete KYC & create a MT5 account on LQHMarkets to qualify for this bonus.</p>
            <p>If you need any assistance, our support team is available 24/7 at support@lqhmarkets.com</p>
            <p>Best Regards.</p>
          <p>LQH Markets Team</p>';
          $templateVars = [
            'name' => 'Valued Client',
            'email' => $settings['email_from_address'],
            "content" => $content,
            "title_right" => "",
            "subtitle_right" => "",
        ];
        $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);
    }
    private function sendWelcomeToExistingUser($user)
    {

        $email=$user->email;
        // $code =Str::random(60);
        // User::where('email', $email)->update(['emailToken' => $code]);
        $settings = settings();
        $from = $settings['email_from_address'];
        $emailSubject = 'Redeem Your $300 SwingTradingLabs Bonus on LQHMarkets';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
        $content ='<div>Thank you for choosing LQH Markets. To activate your SwingTradingLabs $300 bonus, please complete the following steps in order:</div>
            
                
                <p>Fill out your <b>bonus request form</b> here: <ahref="https://forms.gle/Jk9SH1sxM4fEDNre6">https://forms.gle/Jk9SH1sxM4fEDNre6</a></p>
            
            <p><b>Please note:</b>  You must complete KYC & create a MT5 account on LQHMarkets to qualify for this bonus.</p>
            <p>If you need any assistance, our support team is available 24/7 at support@lqhmarkets.com</p>
            <p>Best Regards.</p>
          <p>LQH Markets Team</p>';
        $templateVars = [
            'name' => 'Valued Client',
            'email' => $settings['email_from_address'],
            "content" => $content,
            "title_right" => "",
            "subtitle_right" => "",
        ];
        $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);
    }
}