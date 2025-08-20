<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CustomerScoringService;
use App\Services\CustomerScoreUpdaterService;
use App\Services\ScoringResultFormatterService;
use App\Models\Customer;

/**
 * Console command for recalculating customer scores
 * Follows SRP - only handles command interface and delegates to specialized services
 */
class RecalculateCustomerScores extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'customer:recalculate-scores 
                            {--customer= : Specific customer ID to recalculate}
                            {--all : Recalculate all customers}
                            {--debug : Show detailed scoring breakdown}';

    /**
     * The console command description.
     */
    protected $description = 'Recalculate customer scores and reclassify customer types';

    public function __construct(
        protected CustomerScoringService $scoringService,
        protected CustomerScoreUpdaterService $scoreUpdaterService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $customerId = $this->option('customer');
        $all = $this->option('all');
        $debug = $this->option('debug');

        if ($customerId) {
            return $this->handleSpecificCustomer((int) $customerId, $debug);
        }

        if ($all) {
            return $this->handleAllCustomers($debug);
        }

        $this->error('Please specify either --customer=ID or --all option');
        return 1;
    }

    /**
     * Handle scoring for specific customer
     */
    protected function handleSpecificCustomer(int $customerId, bool $debug): int
    {
        try {
            $customer = Customer::findOrFail($customerId);
            $formatter = ScoringResultFormatterService::forCommand($this, $this->scoringService);
            
            $this->info("Recalculating score for customer: {$customer->name} (ID: {$customerId})");
            
            $result = $this->scoreUpdaterService->updateCustomer($customer, $debug);
            $formatter->displayCustomerResult($result, $debug);
            
            if ($debug) {
                $formatter->displayCustomerData($customer);
            }
            
            return $result->isSuccessful() ? 0 : 1;
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Handle scoring for all customers
     */
    protected function handleAllCustomers(bool $debug): int
    {
        try {
            $this->info("Recalculating scores for all customers...");
            $formatter = ScoringResultFormatterService::forCommand($this, $this->scoringService);
            
            $results = $this->scoreUpdaterService->updateAllCustomers();
            $formatter->displayBatchResults($results, $debug);
            
            return $results['errors'] === 0 ? 0 : 1;
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
}
