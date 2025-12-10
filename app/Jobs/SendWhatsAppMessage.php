<?php
namespace App\Jobs;

use App\Services\WhatsAppCloudService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $payload;
    public int $tries = 3;
    public int $timeout = 30;

    /**
     * @param array $payload - full payload for WhatsAppCloudService (text/template)
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle(WhatsAppCloudService $wa)
    {
        // Decide what to call based on type
        if (($this->payload['type'] ?? '') === 'text') {
            $to = $this->payload['to'] ?? null;
            $body = $this->payload['text']['body'] ?? '';
            $resp = $wa->sendText($to, $body);
        } else {
            $resp = $wa->sendTemplate($this->payload);
        }

        Log::info('[WA JOB] Sent', ['payload' => $this->payload, 'resp' => $resp]);
    }

    public function failed(\Throwable $exception)
    {
        Log::error('[WA JOB] Failed', [
            'exception' => $exception->getMessage(),
            'payload' => $this->payload,
        ]);
    }
}
