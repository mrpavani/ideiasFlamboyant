<?php

declare(strict_types=1);

namespace Services;

/**
 * Wrapper minimalista de requisições HTTP via cURL (usado pelos gateways).
 */
final class Http
{
    /**
     * @return array{status:int, body:array|string, raw:string}
     */
    public static function request(string $method, string $url, array $options = []): array
    {
        $ch = curl_init();

        $headers = $options['headers'] ?? [];
        $body = null;

        if (isset($options['json'])) {
            $body = json_encode($options['json'], JSON_UNESCAPED_UNICODE);
            $headers[] = 'Content-Type: application/json';
        } elseif (isset($options['form'])) {
            $body = http_build_query($options['form']);
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        }

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $options['timeout'] ?? 25,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $raw = (string) curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === '' && $error !== '') {
            return ['status' => 0, 'body' => ['error' => $error], 'raw' => ''];
        }

        $decoded = json_decode($raw, true);

        return [
            'status' => $status,
            'body'   => is_array($decoded) ? $decoded : $raw,
            'raw'    => $raw,
        ];
    }

    public static function get(string $url, array $options = []): array
    {
        return self::request('GET', $url, $options);
    }

    public static function post(string $url, array $options = []): array
    {
        return self::request('POST', $url, $options);
    }
}
