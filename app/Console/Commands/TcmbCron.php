<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class TcmbCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tcmb:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get exchange rates for TRY, USD, EUR from TCMB';

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
        try {
            $client   = new Client();
            $response = $client->get('https://www.tcmb.gov.tr/kurlar/today.xml', [
                'headers' => [
                    'Accept' => 'application/xml',
                ],
            ]);

            $crawler = new Crawler($response->getBody()->getContents());
            $date    = Carbon::createFromFormat('d.m.Y',
                $crawler->filterXPath('//Tarih_Date')->extract(['Tarih'])[0])
                ->format('Y-m-d');

//            $currencyModel = new Currency;
            $crawler->filter('Currency')->each(function (Crawler $row) use ($date) {
                $currency = trim($row->extract(['CurrencyCode'])[0]) . '/TRY';
                $rate     = $row->filter('ForexSelling')->text();
                if ($currency != 'XDR/TRY') {
                    Currency::updateOrCreate(
                        ['currency' => $currency],
                        [
                            'rate' => $rate,
                            'date' => $date,
                        ]
                    );
                }
            });
        } catch (Exception $exception) {
        }

        return 1;
    }
}
