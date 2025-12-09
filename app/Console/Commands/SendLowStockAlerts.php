<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendLowStockAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-low-stock-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email notification for items with low stock';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
