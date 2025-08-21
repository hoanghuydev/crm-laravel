<?php

namespace App\Console\Commands;

use App\Services\OrderNotificationService;
use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;
use Illuminate\Support\Facades\Log;

class OrderNotificationConsumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:consume-order-notifications 
                            {--timeout=0 : Timeout in seconds (0 = no timeout)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume order notification messages from Kafka and send emails';

    protected OrderNotificationService $orderNotificationService;

    public function __construct(OrderNotificationService $orderNotificationService)
    {
        parent::__construct();
        $this->orderNotificationService = $orderNotificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $timeout = (int) $this->option('timeout');
        
        $this->info('Starting Kafka order notifications consumer...');
        $this->info("Topic: " . config('kafka.topics.order_notifications'));
        $this->info("Group ID: " . config('kafka.connections.default.consumer.group_id'));
        
        if ($timeout > 0) {
            $this->info("Timeout: {$timeout} seconds");
        } else {
            $this->info("Running indefinitely (Ctrl+C to stop)");
        }

        try {
            $consumer = Kafka::consumer([config('kafka.topics.order_notifications')])
                ->withBrokers('localhost:9092')
                ->withConsumerGroupId('laravel_order_notifications')
                ->withCommitBatchSize(1)
                ->withAutoCommit(true)
                ->withOptions([
                    'compression.codec' => 'none',
                    'auto.offset.reset' => 'latest'
                ])
                ->withHandler(function ($message) {
                    $this->handleMessage($message);
                });

            $consumer->build()->consume();

            $this->info('Kafka consumer stopped.');
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Kafka consumer error: " . $e->getMessage());
            Log::error("Kafka consumer error", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return self::FAILURE;
        }
    }

    /**
     * Handle incoming Kafka message
     */
    private function handleMessage($message): void
    {
        try {
            $payload = $message->getBody();
            $headers = $message->getHeaders();

            $this->info("Received message from Kafka:");
            $this->line("Headers: " . json_encode($headers));
            $this->line("Payload: " . json_encode($payload, JSON_PRETTY_PRINT));

            // Check message type
            $eventType = $headers['event_type'] ?? 'unknown';
            
            switch ($eventType) {
                case 'order_created':
                    $this->handleOrderCreatedMessage($payload);
                    break;
                case 'test':
                    $this->handleTestMessage($payload);
                    break;
                default:
                    $this->warn("Unknown event type: {$eventType}");
                    Log::warning("Unknown Kafka message event type", [
                        'event_type' => $eventType,
                        'payload' => $payload
                    ]);
            }

        } catch (\Exception $e) {
            $this->error("Error processing message: " . $e->getMessage());
            Log::error("Error processing Kafka message", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'message' => $payload ?? 'N/A'
            ]);
        }
    }

    /**
     * Handle order created message
     */
    private function handleOrderCreatedMessage(array $payload): void
    {
        try {
            $orderId = $payload['order_id'] ?? null;
            $orderNumber = $payload['order_number'] ?? null;
            $customerEmail = $payload['customer']['email'] ?? null;

            if (!$orderId || !$customerEmail) {
                $this->error("Invalid order message: missing order_id or customer email");
                return;
            }

            $this->info("Processing order notification for Order #{$orderNumber} (ID: {$orderId})");
            
            // Send email notification
            $result = $this->orderNotificationService->sendOrderConfirmationEmail($payload);
            
            if ($result) {
                $this->info("✓ Email sent successfully to {$customerEmail}");
                Log::info("Order confirmation email sent", [
                    'order_id' => $orderId,
                    'order_number' => $orderNumber,
                    'customer_email' => $customerEmail
                ]);
            } else {
                $this->error("✗ Failed to send email to {$customerEmail}");
            }

        } catch (\Exception $e) {
            $this->error("Error handling order created message: " . $e->getMessage());
            Log::error("Error handling order created message", [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
        }
    }

    /**
     * Handle test message
     */
    private function handleTestMessage(array $payload): void
    {
        $this->info("✓ Test message received and processed successfully");
        $this->line("Message: " . ($payload['message'] ?? 'No message'));
        
        Log::info("Test Kafka message processed", $payload);
    }
}
