<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Wego\Crawler;

class Crawl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl';

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
        $urlCodes = ['external-hard-disk','power-bank','mobile-phone','usb-flash-memory',
            'notebook-netbook-ultrabook', 'tablet','hair-iron','hair-drier', 'shaver','hair-trimmer','hair-shaping',
            'brake-pad','oil-filter','air-filter','engine-oil','modem-router-adsl','playmobil','memory-cards',
            'smartband','telephone', 'digital-camera','baby-bottle','cell-phone-battery','cordlessscrewdriver',
            'diaper','drill','fax','impact-wrench','intellectual-game','perfume','lego','pacifier-and-accessories',
            'pocket-perfumes', 'keyboard','mouse' ,'air-compressors', 'anglegrinder' , 'blower' , 'door-open',
            'electricshaver', 'glue-gun','heatgun','polisher','sandingmachine','security-alarm','solder-gun','spray-gun',
            'makeup-remover' , 'sunscreen-cream' , 'eye-cream' , 'moisturizing-cream' , 'anti-ageing-cream', 'self-tanning',
            'lightening-cream' , 'anti-acne-cream' , 'cover-cream' , 'peeling-cream','peeling-cream','repair-cream',
            'firming-mask-cream' , 'stretch-marks-repair-cream' , 'anti-spot-repairing-cream','face-masque',
            'skin-serum','face-oil','electric-brusher','tooth-brush','toothpaste','dent-floss','denture%20adhesive',
            'mouthwash','whitening-toothpowder','mouthth-spray','hair-removal-cream','gel-and-foam-reform',
            'after-shave','hair-remover-strips','shave-blade','post-dipilation-gel','roll-on-deodorant','spray-deodorant',
            'stick-deodorant','clear-gel-deodorant','deodorant-cream','saddlecloth-pad','hair-shampoo','hair-lotion',
            'hair-mask', 'lash-and-eyebrow-gel','hair-oil','hair-serum-and-spray','sanitary-pad','body-shampoo',
            'wet-wipes','body-oil-and-lotion','genital-cleaning-gel','antibacterial-soap','tanning-oil',
            'anti-cellulite-cream','soap','baby-oil','lift-bust-cream','cotton-swab','adult-protective-diaper',
            'disinfectants','bath-solt','water-filters','can-opener','kitchen-weighing-scale','chocolate-fountain',
            'refrigerator-freezer','vaccum-cleaner','handheld-vaccum','iron','steam-cleaner','washing-machines',
            'dishwasher','vacuum-cleaner-consumption','hard-floor-carpet-cleaner','diaper-cleaner','oven-toaster',
            'toaster','sandwich-makers','rice-cooker','grill-barbecue','oven','microwave','airfryer','food-processor',
            'mixer' , 'blenders','meat-mincers','chopper','grinder','kitchen%20machine','hand-blender','juicer',
            'coffee-makers','espresso-makers','tea-makers','boiler','water-treatment-and-watercooler','electric-samovar',
            'beverage-maker-accessories','%20citrus-juicer','air-purifier','radiator','heater','fan','iranian-cooler',
            'gas-heater','air%20conditioner','wall-hung-combination-boiler','water-heater','heat-exchanger','sewing-machine',
            'hood','built-in-stove','warmer-drawer','special%20appliances','electrical-samovar','home-electric-accessories',
            'headset','headphone','data-video-projector','home-theatre','home-audio-systems','dvd-player','blu-ray-player',
            'home-multimedia-player','car-player','car-speaker','car-amplifier','incar-accessories','car-navigation',
            'fm-player','station-gaming-consoles','gaming-console-accessories','portable-gaming-consoles','game',
            'ic-rcorder','portable-music-video-player','power-strip','portable-players-accessories','audio-and-video-accessories',
            'audiotransmitterreceiver','remote-control','bracket','radio','amplifier','tv2','usb-dvb-t','set-top-box',
            'cookwareset'
        ];
        // inha ke comment shode baraye chiye ?
          $fileCount = Crawler::crawlCategories($urlCodes,Crawler::EXIST); // in baraye chiye : code digikala be hamrah gheymat baraye update gheymat
//        $fileCount = Crawler::crawlCategories($urlCodes,Crawler::NOT_EXIST,$fileCount);
        //alan 2 ta command shode ino bezar baraye update gheymat un yekio barayae kalahaye jadid
//        Crawler::crawlNewCategory(['external-hard-disk']);

    }
}
