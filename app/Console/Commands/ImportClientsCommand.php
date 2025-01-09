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
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
        $this->mailService = $mailService;
        // $this->api = $api;
        $filePath = $this->argument('file');
        $missingcountries=0;
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
                    $this->error("Row {$record['Where are you from?']}: Country not found");
                    continue;
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
                    if(empty( $existing->ib1) || $existing->ib1=='noIB'){
                        $existing->ib1='Swingtradinglab';
                        $accountcount=$existing->accounts()->count();
                        foreach($existing->accounts as $account){
                            dump($account->accountType);
                           
                           $groupCode = str_replace("DF","ALEX",$account->accountType->ac_group);
                            $group = AccountType::where('ac_group', $groupCode)->first();
                            if($group){
                                $_POST["options"] =$group->id;
                                $account_type_id = $group->id;
                                $code=$account->code;
                                dump($account_type_id);
                                if (($error_code = $this->api->UserGet($code, $trade_user)) != MTRetCode::MT_RET_OK) {
                                    //dd(MTRetCode::GetError($error_code));
                                    // return response()->json([
                                    //     'status' => 'warning',
                                    //     'message' => 'Something went wrong on Updating details',
                                    //     'error' => MTRetCode::GetError($error_code)
                                    // ], 400);
                                    $this->error( 'Something went wrong on Updating details '.$code." " . MTRetCode::GetError($error_code));
                                }
                                dd($trade_user);
                                // Fetch account type details
                                $trade_user->Group = $group->ac_group;
                    
                                // Update user data via API
                                $updated_user = "";
                                if (($error_code = $this->api->UserUpdate($trade_user, $updated_user)) != MTRetCode::MT_RET_OK) {
                                    $this->error( "Something went wrong on Updating details $code " . MTRetCode::GetError($error_code));
                                } else {
                                    $account->account_type_id = $account_type_id;
                                    // $account->save();
                                    
                                }
                            }
                        }
                        $this->info("Row {$record['Your email address']}: User already exists and has $accountcount accounts");
                        // $existing->save();
                    }else{
                        $this->error("Row {$record['Your email address']}: User already exists and has an IB1");
                    }
                    continue;
                }else{
                    // User::create([
                    //     'fullname'=>$record['Full Name'],
                    //     'email'=>$record['Your email address'],
                    //     'username'=>$record['Your email address'],
                    //     'password'=>Hash::make('password'.rand(999,999999)),
                    //     'country_code'=>$country->country_code,
                    //     'number'=>$phone,
                    //     'country'=>$record['Where are you from?'],
                    //     // 'request'=>$record['What do you want?'],
                    //     'wallet_enabled'=>1,
                    //     'ib1'=>'Swingtradinglab'
                    // ]);
                }
                // die();
            }
        } catch (\Exception $e) {
            $this->error('An error occurred while parsing the CSV file: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info('CSV parsing completed successfully. Ignored '.$missingcountries.' rows with missing countries.');
        return Command::SUCCESS;
    }
}