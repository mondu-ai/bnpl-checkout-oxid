<?php

namespace OxidEsales\MonduPayment\Core\Http;

use OxidEsales\MonduPayment\Core\Exception\InvalidRequestException;

class HttpRequest
{
    /**
     * curl instance
     *
     * @var [curl]
     */
    private $curl;

    /**
     * headers of the request
     *
     * @var array
     */
    private array $headers;

    /**
     * baseUrl of the request
     *
     * @var array
     */
    private string $baseUrl;

    public function __construct(string $baseUrl, array $headers = ['Content-type' => 'application/json'])
    {
        $this->curl = curl_init();
        $this->baseUrl = $baseUrl;
        $this->headers = $headers;
    }

    /**
     * get Request
     *
     * @param string $url
     * @param array $data
     * @param array $headers
     * @return void
     */
    public function get(string $url, array $data = [], array $headers = null)
    {
        $url = $this->baseUrl . $url;
        $this->headers = $headers == null ? $this->headers : $headers;
        return $this->send_request($url, $data, 'GET');
    }

    /**
     * POST Request
     *
     * @param string $url
     * @param array $data
     * @param array $headers
     * @return void
     */
    public function post(string $url, array $data = [], array $headers = null)
    {
        $url = $this->baseUrl . $url;
        $this->headers = $headers == null ? $this->headers : $headers;
        return $this->send_request($url, $data, 'POST');
    }

    /**
     * PATCH Request
     *
     * @param string $url
     * @param array $data
     * @param array $headers
     * @return void
     */
    public function patch(string $url, array $data = [], array $headers = ['Content-type' => 'application/json'])
    {
        $url = $this->baseUrl . $url;
        $this->headers = $headers;
        return $this->send_request($url, $data, 'PATCH');
    }

    /**
     * PUT Request
     *
     * @param string $url
     * @param array $data
     * @param array $headers
     * @return void
     */
    public function put(string $url, array $data = [], array $headers = ['Content-type' => 'application/json'])
    {
        $url = $this->baseUrl . $url;
        $this->headers = $headers;
        return $this->send_request($url, $data, 'PUT');
    }

    /**
     * DELETE Request
     *
     * @param string $url
     * @param array $data
     * @param array $headers
     * @return void
     */
    public function delete(string $url, array $data = [], array $headers = ['Content-type' => 'application/json'])
    {
        $url = $this->baseUrl . $url;
        $this->headers = $headers;
        return $this->send_request($url, $data, 'DELETE');
    }

    /**
     * for sending request
     *
     * @param string $url
     * @param array|null $data
     * @param string $method
     * @return array $response
     * @throws InvalidRequestException
     */
    public function send_request(string $url, ?array $data, string $method)
    {
        $this->curl = curl_init();

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);

        if ($method === 'POST') {
            curl_setopt($this->curl, CURLOPT_POST, true);
            if ($data) curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($this->curl);
        $httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        curl_close($this->curl);

        if (!$response && $httpCode > 308) {
            throw new InvalidRequestException('[MONDU__ERROR] Request can not be processed.', $data);
        }

        $response = json_decode($response, true);

        if (
            (isset($response['errors']) && $response['errors'] != null) ||
            (isset($response['error']) && $response['error'] != null)
        ) {
            throw new InvalidRequestException('[MONDU__ERROR] ' . json_encode($response), $data, $response);
        }

        return $response;
    }
}
