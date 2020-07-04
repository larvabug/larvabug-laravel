<?php

namespace LarvaBug\Commands;

use Illuminate\Console\Command;

class LarvaBugTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larvabug:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Larvabug test command to check configurations, and send test exception';

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
        $this->info('Testing LarvaBug Configurations');

        if (config('larvabug.project_id')){
            $this->info('1. ✓ [Larvabug] Found project id');
        }else{
            $this->info('1. ✗ [Larvabug] Could not find your project id, please set this in your .env');
        }

        if (config('larvabug.project_secret')){
            $this->info('2. ✓ [Larvabug] Found secret key');
        }else{
            $this->info('2. ✗ [Larvabug] Could not find LarvaBug secret, please set this in your .env');
        }

        $requestData = [
            'projectId' => config('larvabug.project_id'),
            'projectSecret' => config('larvabug.project_secret')
        ];

        if (app('larvabug') && app('larvabug')->validateCredentials($requestData)){
            $this->info('3. ✓ [Larvabug] Validation Success');
        }else{
            $this->info('3. ✗ [Larvabug] Project id and secret do not match our records');
        }

    }

}