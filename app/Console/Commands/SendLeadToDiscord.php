<?php

namespace App\Console\Commands;

use App\Models\Lead;
use Illuminate\Console\Command;
use App\Services\LeadService;

class SendLeadToDiscord extends Command
{
    protected $signature = 'lead:send-discord';
    protected $description = 'Send unverified user lead to Discord if not sent before';

    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        parent::__construct();
        $this->leadService = $leadService;
    }

    public function handle(): int
    {
        $lead = Lead::whereNull('verified_at')
            ->whereNull('send_discord')
            ->first();

        if ($lead) {
            $leadArray = $lead->toArray();

            if ($this->leadService->sendLeadToDiscord($leadArray)) {
                $lead->update(['send_discord' => 1]);
                $this->info("Lead {$lead->id} has been sent.");
            }
        } else {
            $this->info("No unsent leads found.");
        }

        return Command::SUCCESS;
    }
}
