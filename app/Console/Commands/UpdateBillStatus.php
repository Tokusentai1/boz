<?php

namespace App\Console\Commands;

use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateBillStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-bill-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Get the current time
        $currentTime = Carbon::now();

        // Find bills that are 'ongoing' and were created more than 15 minutes ago
        $bills = Bill::where('status', 'ongoing')
            ->where('created_at', '<=', $currentTime->subMinutes(15))
            ->get();

        // Update the status of these bills to 'shipping'
        foreach ($bills as $bill) {
            $bill->status = 'shipping';
            $bill->save();
        }

        $this->info('Bill statuses updated successfully.');
    }
}
