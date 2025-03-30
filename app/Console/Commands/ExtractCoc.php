<?php

namespace App\Console\Commands;

use App\Services\ExtractService;
use App\Services\ExtractZeroPromptService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExtractCoc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:extract-coc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Experiment on extracting data from conformity certificate.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $image = Storage::disk('static')->get('FSP-2018-1188-DoC.png');

//        $data = app(ExtractService::class)->handle($image);
        $data = app(ExtractZeroPromptService::class)->handle($image);

        dump($data->answer);
        dump($data->certificate);
    }
}
