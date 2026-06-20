<?php
namespace Plugin\GlobalIm\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Plugin\GlobalIm\Models\ImMessage;

class GlobalImService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function enabled(): bool
    {
        return (int) plugin_setting('global_im', 'enabled', 0) === 1;
    }

    public function defaultChannel(): string
    {
        return (string) plugin_setting('global_im', 'default_channel', 'telegram');
    }

    public function logInbound(string $channel, string $peerId, string $body, array $payload = []): ImMessage
    {
        return ImMessage::query()->create([
            'channel'   => $channel,
            'direction' => 'in',
            'peer_id'   => $peerId,
            'body'      => $body,
            'payload'   => $payload,
        ]);
    }

    public function send(string $channel, string $peerId, string $body): ImMessage
    {
        if ($channel === 'whatsapp') {
            $this->sendWhatsApp($peerId, $body);
        } else {
            $this->sendTelegram($peerId, $body);
        }

        return ImMessage::query()->create([
            'channel'   => $channel,
            'direction' => 'out',
            'peer_id'   => $peerId,
            'body'      => $body,
            'payload'   => [],
        ]);
    }

    protected function sendTelegram(string $chatId, string $text): void
    {
        $token = (string) plugin_setting('global_im', 'telegram_bot_token', '');
        if ($token === '') {
            throw new Exception(__('GlobalIm::common.no_telegram'));
        }

        $resp = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text'    => $text,
        ]);

        if (! $resp->successful()) {
            throw new Exception($resp->json('description') ?? 'Telegram API error');
        }
    }

    protected function sendWhatsApp(string $to, string $text): void
    {
        $token   = (string) plugin_setting('global_im', 'whatsapp_token', '');
        $phoneId = (string) plugin_setting('global_im', 'whatsapp_phone_id', '');
        if ($token === '' || $phoneId === '') {
            throw new Exception(__('GlobalIm::common.no_whatsapp'));
        }

        $resp = Http::withToken($token)
            ->post("https://graph.facebook.com/v19.0/{$phoneId}/messages", [
                'messaging_product' => 'whatsapp',
                'to'                => preg_replace('/\D+/', '', $to),
                'type'              => 'text',
                'text'              => ['body' => $text],
            ]);

        if (! $resp->successful()) {
            throw new Exception($resp->json('error.message') ?? 'WhatsApp API error');
        }
    }

    public function handleTelegramWebhook(array $payload): void
    {
        $msg    = $payload['message'] ?? [];
        $chatId = (string) ($msg['chat']['id'] ?? '');
        $text   = (string) ($msg['text'] ?? '');
        if ($chatId !== '' && $text !== '') {
            $this->logInbound('telegram', $chatId, $text, $payload);
        }
    }

    public function handleWhatsAppWebhook(array $payload): void
    {
        $entry = $payload['entry'][0]['changes'][0]['value']['messages'][0] ?? null;
        if (! $entry) {
            return;
        }
        $from = (string) ($entry['from'] ?? '');
        $text = (string) ($entry['text']['body'] ?? '');
        if ($from !== '' && $text !== '') {
            $this->logInbound('whatsapp', $from, $text, $payload);
        }
    }
}
