<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapGenerator;

class GenSitemap extends Command
{
    /** This is the SiteMap Generator , which is a command and would run with 'php artisan gensitema'
     *  Do Remember : that the commands , including this one must be Registered in the Kernel
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generatesitemap';

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

    }
}
