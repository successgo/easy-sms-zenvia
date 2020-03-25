<?php

namespace SuccessGo\EasySms\Gateways;

use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Gateways\Gateway;
use Overtrue\EasySms\Support\Config;
use Overtrue\EasySms\Traits\HasHttpRequest;

/**
 * Class ZenviaGateway
 *
 * @see https://zenviasmsenus.docs.apiary.io/#
 * @copyright Success Go <successgao@protonmail.ch>
 */
class ZenviaGateway extends Gateway
{
    use HasHttpRequest;

    const ENDPOINT_URL = 'https://api-rest.zenvia.com/services/send-sms';

    const STATUS_OK = '00';

    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $IDDCode = !empty($to->getIDDCode()) ? $to->getIDDCode() : 55;

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($config['account'].':'.$config['password']),
        ];
        $payload = [
            'msg' => $message->getContent(),
            'to' => $IDDCode . $to->getNumber(),
        ];
        $params = [
            'sendSmsRequest' => $payload,
        ];
        $result = $this->postJson(self::ENDPOINT_URL, $params, $headers);

        $response = $result['sendSmsResponse'] ?? [];
        if (!$this->isResponseOk($response)) {
            throw new GatewayErrorException(json_encode($result, JSON_UNESCAPED_UNICODE), isset($response['statusCode']) ? $response['statusCode'] : 0, $result);
        }

        return $result;
    }

    public function getName()
    {
        return 'Zenvia';
    }

    private function isResponseOk(array $response)
    {
        if ($response['statusCode'] && $response['statusCode'] == self::STATUS_OK) {
            return true;
        }

        return false;
    }
}
