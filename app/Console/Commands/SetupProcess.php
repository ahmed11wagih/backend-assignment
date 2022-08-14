<?php

namespace App\Console\Commands;

use App\Models\ShipPosition;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SetupProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command starts the setup process of the project, which creates the db & tables and insert the data of the src file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Setup process ....');

        $file = public_path('files/ship_positions.json');
        $stream = fopen($file, 'r') or $this->info('Unable to fetch the data');
        $data = json_decode(fread($stream,filesize($file)),true);
        fclose($stream);

        Artisan::call('migrate');

        foreach($data as $position) ShipPosition::updateOrCreate($position);

        $this->info('Setup process DONE');
    }
}
