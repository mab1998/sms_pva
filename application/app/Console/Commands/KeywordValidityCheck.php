<?php

namespace App\Console\Commands;

use App\Keywords;
use Illuminate\Console\Command;

class KeywordValidityCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keyword:checkvalidity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Keyword time validity check';

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
     * @return mixed
     */
    public function handle()
    {
        $keywords = Keywords::where('status', 'assigned')->where('user_id','!=','0')->where('validity','!=','0')->get();
        foreach ($keywords as $kw) {
            if (new \DateTime() >= new \DateTime($kw->validity_date)) {
                $kw->status = 'expired';
                $kw->save();
            }
        }
    }
}
