<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ReadImportFile extends Command
{
    /**
     * The name and signature of the console command.
     * Group_Code_Alex_Yes_Bonus.csv
     * @var string
     */
    protected $signature = 'app:read-import-file {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set("max_execution_time", 0);
        $file = $this->argument('file');
        $file = fopen($file, 'r');
        $header = fgetcsv($file);
        $data = [];
        while ($row = fgetcsv($file)) {
            $data[] = array_combine($header, $row);
            Artisan::call('app:update-leverage',[
                'account_code'=>$row[3],
                'leverage'=>50
            ]);
            //print last command output
            echo Artisan::output();
            
        }
        fclose($file);
    }
}
