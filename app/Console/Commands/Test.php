<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sample:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test add';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $data['name']="angelica";
        // \DB::table('test')->insert($data);
        // $this->info('Success: ' . json_encode($check));
        // $check = DB::connection('pis')->table('personal_information')->first();

    }
}
