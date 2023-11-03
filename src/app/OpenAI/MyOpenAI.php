<?php

namespace App\OpenAI;

use Orhanerday\OpenAi\OpenAi;
use Orhanerday\OpenAi\Url;
use Exception;

class MyOpenAI extends OpenAi
{
    private string $instructModel = "gpt-3.5-turbo-instruct";
    private array $headers;
    private array $contentTypes;
    private int $timeout = 0;
    private object $stream_method;
    private string $proxy = "";
    private array $curlInfo = [];

    public function __construct($OPENAI_API_KEY)
    {
        parent::__construct($OPENAI_API_KEY);
        $this->contentTypes = [
            "application/json"    => "Content-Type: application/json",
            "multipart/form-data" => "Content-Type: multipart/form-data",
        ];
        $this->headers = [
            $this->contentTypes["application/json"],
            "Authorization: Bearer $OPENAI_API_KEY",
        ];
    }

    public function instruct($opts, $stream = null)
    {
        if ($stream != null && array_key_exists('stream', $opts)) {
            if (!$opts['stream']) {
                throw new Exception(
                    'Please provide a stream function. Check https://github.com/orhanerday/open-ai#stream-example for an example.'
                );
            }
            $this->stream_method = $stream;
        }

        $opts['model'] = $opts['model'] ?? $this->instructModel;
        $url           = self::instructUrl();

        return $this->sendRequest($url, 'POST', $opts);
    }

    public static function instructUrl(): string
    {
        return Url::OPEN_AI_URL . "/completions";
    }

    private function sendRequest(string $url, string $method, array $opts = [])
    {
        $post_fields = json_encode($opts);

        if (array_key_exists('file', $opts) || array_key_exists('image', $opts)) {
            $this->headers[0] = $this->contentTypes["multipart/form-data"];
            $post_fields      = $opts;
        } else {
            $this->headers[0] = $this->contentTypes["application/json"];
        }
        $curl_info = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => $post_fields,
            CURLOPT_HTTPHEADER     => $this->headers,
        ];

        if ($opts == []) {
            unset($curl_info[CURLOPT_POSTFIELDS]);
        }

        if (!empty($this->proxy)) {
            $curl_info[CURLOPT_PROXY] = $this->proxy;
        }

        if (array_key_exists('stream', $opts) && $opts['stream']) {
            $curl_info[CURLOPT_WRITEFUNCTION] = $this->stream_method;
        }

        $curl = curl_init();

        curl_setopt_array($curl, $curl_info);
        $response = curl_exec($curl);

        $info           = curl_getinfo($curl);
        $this->curlInfo = $info;

        curl_close($curl);

        return $response;
    }
}
