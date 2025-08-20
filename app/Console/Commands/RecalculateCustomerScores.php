<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CustomerScoringService;
use App\Models\Customer;

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

    protected CustomerScoringService $scoringService;

    public function __construct(CustomerScoringService $scoringService)
    {
        parent::__construct();
        $this->scoringService = $scoringService;
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
            return $this->recalculateSpecificCustomer($customerId, $debug);
        }

        if ($all) {
            return $this->recalculateAllCustomers($debug);
        }

        $this->error('Please specify either --customer=ID or --all option');
        return 1;
    }

    /**
     * Recalculate scores for specific customer
     */
    protected function recalculateSpecificCustomer(int $customerId, bool $debug): int
    {
        try {
            $customer = Customer::findOrFail($customerId);
            
            $this->info("Recalculating score for customer: {$customer->name} (ID: {$customerId})");
            
            $oldScore = $customer->current_score;
            $oldType = $customer->customerType?->name ?? 'Unknown';
            
            $wasReclassified = $this->scoringService->reclassifyCustomer($customer);
            
            $customer->refresh();
            
            $this->info("Results:");
            $this->line("  Old Score: {$oldScore}");
            $this->line("  New Score: {$customer->current_score}");
            $this->line("  Old Type: {$oldType}");
            $this->line("  New Type: {$customer->customerType->name}");
            $this->line("  Reclassified: " . ($wasReclassified ? 'Yes' : 'No'));
            
            if ($debug) {
                $this->showScoreBreakdown($customer);
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Recalculate scores for all customers
     */
    protected function recalculateAllCustomers(bool $debug): int
    {
        try {
            $this->info("Recalculating scores for all customers...");
            
            $results = $this->scoringService->reclassifyAllCustomers();
            
            $this->info("Batch recalculation completed!");
            $this->line("  Total customers: {$results['total']}");
            $this->line("  Reclassified: {$results['reclassified']}");
            $this->line("  Errors: {$results['errors']}");
            
            if ($debug && $results['total'] > 0) {
                $this->info("\nDetailed breakdown:");
                $customers = Customer::with('customerType')->get();
                
                $this->table(
                    ['ID', 'Name', 'Score', 'Type', 'Orders', 'Total Spent', 'Location'],
                    $customers->map(function ($customer) {
                        return [
                            $customer->id,
                            $customer->name,
                            number_format($customer->current_score, 3),
                            $customer->customerType?->name ?? 'N/A',
                            $customer->getTotalOrderCount(),
                            number_format($customer->getTotalSpent()),
                            $customer->isFromHCM() ? 'HCM' : 'Other',
                        ];
                    })->toArray()
                );
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Show detailed score breakdown for a customer
     */
    protected function showScoreBreakdown(Customer $customer): void
    {
        $breakdown = $this->scoringService->getCustomerScoreBreakdown($customer);
        
        $this->info("\nDetailed Score Breakdown:");
        
        foreach ($breakdown['breakdown'] as $metric => $data) {
            $this->line("  {$metric}:");
            $this->line("    Raw Score: " . number_format($data['raw_score'], 3));
            $this->line("    Weight: " . number_format($data['weight'], 2));
            $this->line("    Weighted: " . number_format($data['weighted_score'], 3));
        }
        
        $this->info("\nCustomer Data:");
        $this->line("  Total Spent: " . number_format($customer->getTotalSpent()));
        $this->line("  Total Orders: " . $customer->getTotalOrderCount());
        $this->line("  Avg Days Between Orders: " . number_format($customer->getAverageDaysBetweenOrders(), 1));
        $this->line("  From HCM: " . ($customer->isFromHCM() ? 'Yes' : 'No'));
        $this->line("  Last Score Calculated: " . ($customer->last_score_calculated_at ?? 'Never'));
    }
}
