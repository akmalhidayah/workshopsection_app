<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppCloudService
{
    protected ?string $token;
    protected ?string $phoneNumberId;
    protected string $version;

    public function __construct()
    {
        // Ambil dari config services.whatsapp, kalau null fallback ke env()
        $this->token = config('services.whatsapp.token') ?? env('WA_CLOUD_TOKEN');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id') ?? env('WA_PHONE_NUMBER_ID');
        $this->version = config('services.whatsapp.version', env('WA_API_VERSION', 'v22.0'));

        // Debug sekali saat init (bisa dihapus kalau sudah aman)
        Log::debug('[WA Cloud] init config', [
            'token_present'    => !empty($this->token),
            'phone_number_id'  => $this->phoneNumberId,
            'version'          => $this->version,
        ]);
    }

    protected function endpoint(): string
    {
        return "https://graph.facebook.com/{$this->version}/{$this->phoneNumberId}/messages";
    }

    /**
     * Send plain text message.
     * @param string $to  E.164 without plus, e.g. 6281234567890
     * @param string $body
     * @return array
     */
    public function sendText(string $to, string $body): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $body],
        ];

        return $this->post($payload);
    }

    /**
     * Send template message (caller must prepare valid template payload)
     * @param array $payload
     * @return array
     */
    public function sendTemplate(array $payload): array
    {
        return $this->post($payload);
    }

    protected function post(array $payload): array
    {
        if (empty($this->token) || empty($this->phoneNumberId)) {
            $msg = 'WhatsApp Cloud token or phone number id is not configured.';
            Log::error('[WA Cloud] config missing: ' . $msg, [
                'config_whatsapp' => config('services.whatsapp'),
                'env_token_present' => !empty(env('WA_CLOUD_TOKEN')),
                'env_phone_id'      => env('WA_PHONE_NUMBER_ID'),
            ]);
            return ['error' => true, 'message' => $msg];
        }

        try {
            $endpoint = $this->endpoint();

            // request
            $res = Http::withToken($this->token)
                ->acceptJson()
                ->post($endpoint, $payload);

            // debug logs (reduce/disable in prod if too verbose)
            Log::debug('[WA Cloud] request', ['endpoint' => $endpoint, 'payload' => $payload]);
            Log::debug('[WA Cloud] response', ['status' => $res->status(), 'body' => $res->body()]);

            if ($res->successful()) {
                return $res->json();
            }

            // non-2xx responses
            return [
                'error' => true,
                'status' => $res->status(),
                'body' => $res->json() ?? $res->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('[WA Cloud] exception: ' . $e->getMessage(), ['payload' => $payload]);
            return ['error' => true, 'exception' => $e->getMessage()];
        }
    }
}
