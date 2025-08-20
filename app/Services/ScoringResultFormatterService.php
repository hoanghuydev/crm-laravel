<?php

namespace App\Services;

use App\DTOs\CustomerScoringResultDTO;
use App\Models\Customer;
use Illuminate\Console\Command;

/**
 * Service for formatting customer scoring results for console output
 * Follows SRP - only handles output formatting
 */
class ScoringResultFormatterService
{
    public function __construct(
        protected Command $command,
        protected CustomerScoringService $scoringService
    ) {}

    /**
     * Display single customer scoring result
     */
    public function displayCustomerResult(CustomerScoringResultDTO $result, bool $includeBreakdown = false): void
    {
        if (!$result->isSuccessful()) {
            $this->command->error("Error updating customer {$result->customer->id}: {$result->error?->getMessage()}");
            return;
        }

        $this->command->info("Results for customer: {$result->customer->name} (ID: {$result->customer->id})");
        $this->command->line("  Old Score: " . number_format($result->oldScore, 3));
        $this->command->line("  New Score: " . number_format($result->newScore, 3));
        $this->command->line("  Score Change: " . ($result->getScoreChange() > 0 ? '+' : '') . number_format($result->getScoreChange(), 3));
        $this->command->line("  Old Type: {$result->getOldTypeName()}");
        $this->command->line("  New Type: {$result->getNewTypeName()}");
        $this->command->line("  Reclassified: " . ($result->wasReclassified ? 'Yes' : 'No'));

        if ($includeBreakdown && !empty($result->scoreBreakdown)) {
            $this->displayScoreBreakdown($result->scoreBreakdown);
        }
    }

    /**
     * Display batch operation results
     */
    public function displayBatchResults(array $batchResults, bool $includeDetails = false): void
    {
        $this->command->info("Batch customer scoring completed!");
        $this->command->line("  Total customers: {$batchResults['total']}");
        $this->command->line("  Successful updates: {$batchResults['successful']}");
        $this->command->line("  Reclassified: {$batchResults['reclassified']}");
        $this->command->line("  Errors: {$batchResults['errors']}");

        if ($includeDetails && !empty($batchResults['details'])) {
            $this->displayDetailedBatchResults($batchResults['details']);
        }
    }

    /**
     * Display detailed batch results in table format
     */
    protected function displayDetailedBatchResults(array $results): void
    {
        $this->command->info("\nDetailed breakdown:");
        
        $tableData = [];
        foreach ($results as $result) {
            if ($result instanceof CustomerScoringResultDTO && $result->isSuccessful()) {
                $customer = $result->customer;
                $tableData[] = [
                    $customer->id,
                    $customer->name,
                    number_format($result->newScore, 3),
                    $result->getNewTypeName(),
                    $customer->getTotalOrderCount(),
                    number_format($customer->getTotalSpent()),
                    $customer->isFromHCM() ? 'HCM' : 'Other',
                    $result->wasReclassified ? 'Yes' : 'No',
                ];
            }
        }

        if (!empty($tableData)) {
            $this->command->table(
                ['ID', 'Name', 'Score', 'Type', 'Orders', 'Total Spent', 'Location', 'Reclassified'],
                $tableData
            );
        }
    }

    /**
     * Display detailed score breakdown
     */
    public function displayScoreBreakdown(array $breakdown): void
    {
        if (isset($breakdown['breakdown'])) {
            $breakdown = $breakdown['breakdown'];
        }

        $this->command->info("\nDetailed Score Breakdown:");
        
        foreach ($breakdown as $metric => $data) {
            $this->command->line("  {$metric}:");
            
            if (is_array($data)) {
                $this->command->line("    Raw Score: " . number_format($data['raw_score'] ?? 0, 3));
                $this->command->line("    Weight: " . number_format($data['weight'] ?? 0, 2));
                $this->command->line("    Weighted: " . number_format($data['weighted_score'] ?? 0, 3));
            } else {
                $this->command->line("    Score: " . number_format($data, 3));
            }
        }
    }

    /**
     * Display customer additional data
     */
    public function displayCustomerData(Customer $customer): void
    {
        $this->command->info("\nCustomer Data:");
        $this->command->line("  Total Spent: " . number_format($customer->getTotalSpent()));
        $this->command->line("  Total Orders: " . $customer->getTotalOrderCount());
        $this->command->line("  Avg Days Between Orders: " . number_format($customer->getAverageDaysBetweenOrders(), 1));
        $this->command->line("  From HCM: " . ($customer->isFromHCM() ? 'Yes' : 'No'));
        $this->command->line("  Last Score Calculated: " . ($customer->last_score_calculated_at ?? 'Never'));
    }

    /**
     * Create formatter instance for command
     */
    public static function forCommand(Command $command, CustomerScoringService $scoringService): self
    {
        return new self($command, $scoringService);
    }
}
