<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Wego\Crawler;

class NewCrawl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newcrawl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        Crawler::crawlNewCategory(['external-hard-disk']); // chera in injast ? chera faghat external-hard-disk hast ?
        //har goruh kalayi ro ke khasti az digikala begiri mizari in tu command ro seda mizani
    }
}
