<?php
namespace Cloudstuff\ApiUtil;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

/**
 * API Class which is used to make the requests to the ZohoBooks by properly
 * forming the request URL, params etc and handling authentication
 *
 * @package Cloudstuff Standard Shared Libraries
 * @author Hemant Mann <hemant.mann@cloudstuff.tech>
 * @since 1.0.0
 */
class Api {
    /**
     * Number of seconds to wait before marking the request as timed out
     * @var integer
     */
    protected $defaultTimeout = 10;

    /**
     * Default Headers to be set for the guzzle client
     * @var array
     */
    protected $defaultHeaders = [];

    /**
     * Base URI
     * @var string
     */
    protected $baseUri;

    /**
     * Store the Guzzle Client
     * @var Client
     */
    protected $client;

    /**
     * Initialize the class object by setting required parameters
     * @param string $authToken ZohoBooks auth token
     */
    public function __construct(array $opts = []) {
        foreach ($opts as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        $guzzleOpts = [ 'timeout'  => $this->defaultTimeout, 'headers' => $this->defaultHeaders ];
        if ($this->baseUri) {
            $guzzleOpts['base_uri'] = $this->baseUri;
        }
        $this->client = new Client($guzzleOpts);
    }

    /**
     * Make a request to the API, catch exceptions if any and convert them to
     * SDK exceptions so that caller is easily able to catch them
     * @param  string $method request METHOD
     * @param  string $path   Path to resource
     * @param  array  $opts   Guzzle Options array
     * @throws Exception\Core Library base exception class
     * @return Response         response object
     */
    private function request(string $method, string $path, array $opts) {
        try {
            $resp = $this->client->request($method, $path, $opts);
        } catch (\GuzzleHttp\Exception\ClientException $e) {    // 4xx errors
            throw new Exception\Response($e->getMessage(), 400, $e);
        } catch (\GuzzleHttp\Exception\ServerException $e) {    // 5xx errors
            throw new Exception\Response($e->getMessage(), 500, $e);
        } catch (\GuzzleHttp\Exception\TransferException $e) {  // any other error
            throw new Exception\Core($e->getMessage(), 400, $e);
        }
        return $resp;
    }

    /**
     * Get the body from Guzzle response object and json decode the body
     * since the API will only return JSON response always
     * @param  Response $resp Guzzle Response object
     * @return stdClass         {headers: assoc array, body: string}
     */
    private function sendResp(Response $resp) {
        $body = $resp->getBody();
        $data = $body->getContents();
        $arr = ['headers' => $resp->getHeaders(), 'body' => $data];
        return (object) $arr;
    }

    /**
     * Make a GET request to the specified endpoint
     * @param  string $path Full Url to the resource
     * @param  array  $opts Additional options array to be passed
     * @throws Exception\Core|Exception\Response
     * @return object       {headers: assoc array, body: string}
     */
    public function get(string $url, array $opts = []) {
        $resp = $this->request('GET', $url, $opts);
        return $this->sendResp($resp);
    }

    /**
     * Make a POST request to the specified endpoint
     * @param  string $url Full Url to the resource
     * @param  array  $opts Additional options array to be passed
     * @throws Exception\Core|Exception\Response
     * @return object       {headers: assoc array, body: string}
     */
    public function post(string $url, array $opts = []) {
        $resp = $this->request('POST', $url, $opts);
        return $this->sendResp($resp);
    }

    /**
     * Make a PUT request to the specified endpoint
     * @param  string $url Full Url to the resource
     * @param  array  $opts Additional options array to be passed
     * @throws Exception\Core|Exception\Response
     * @return object       {headers: assoc array, body: string}
     */
    public function put(string $url, array $opts = []) {
        $resp = $this->request('PUT', $url, $opts);
        return $this->sendResp($resp);
    }

    public function getAsync(string $url) {
        return $this->client->getAsync($url);
    }

    /**
     * Make a DELETE request to the specified endpoint
     * @param string $url Full url to the resource
     * @param array $opts Additional options array to be passed
     * @throws Exception\Core|Exception\Response
     * @return object       {headers: assoc array, body: string}
     */
    public function delete(string $url, array $opts = []) {
        $resp = $this->request('DELETE', $url, $opts);
        return $this->sendResp($resp);
    }
}
