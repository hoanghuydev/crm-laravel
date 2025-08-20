<?php

namespace App\Console\Commands;

use App\Services\KafkaProducerService;
use Illuminate\Console\Command;

class TestKafkaIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:test-integration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Kafka integration by sending a test message';

    protected KafkaProducerService $kafkaProducerService;

    public function __construct(KafkaProducerService $kafkaProducerService)
    {
        parent::__construct();
        $this->kafkaProducerService = $kafkaProducerService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing Kafka integration...');

        try {
            $testData = [
                'test_id' => uniqid(),
                'message' => 'Kafka integration test from Laravel',
                'environment' => config('app.env'),
                'timestamp' => now()->toISOString(),
            ];

            $result = $this->kafkaProducerService->sendTestMessage($testData);

            if ($result) {
                $this->info('✓ Test message sent successfully to Kafka!');
                $this->line('Topic: ' . config('kafka.topics.order_notifications'));
                $this->line('Data: ' . json_encode($testData, JSON_PRETTY_PRINT));
                
                $this->newLine();
                $this->comment('Now you can run the consumer to see the message:');
                $this->line('php artisan kafka:consume-order-notifications --timeout=30');
                
                return self::SUCCESS;
            } else {
                $this->error('✗ Failed to send test message to Kafka');
                return self::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('Error testing Kafka integration: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
