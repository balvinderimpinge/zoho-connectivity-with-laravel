<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller as Controller;
use GuzzleHttp\Client;

class APIBaseController extends Controller
{
    public $baseUri;
    public $secret;

    /**
     * @param       $method
     * @param array $formParams
     * @param array $headers
     *
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($method, $formParams = [], $headers = []) : string
    {
        $client = new Client([
            'base_uri' => $this->baseUri
        ]);

        if (isset($this->secret)) {
            $headers['Authorization'] = $this->secret;
            $headers['content-type'] = 'application/json';
        }
        
        $response = $client->request($method, '',
            [
                'body' => json_encode($formParams),
                'headers' => $headers
            ]
        );
        return $response->getBody()->getContents();
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message) {
    	$response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404) {
    	$response = [
            'success' => false,
            'message' => $error,
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    /**
     * @param     $data
     * @param int $statusCode
     *
     * @return mixed
     */
    public function successResponse($data, $statusCode = Response::HTTP_OK) {
        return response($data, $statusCode)->header('Content-Type', 'application/json');
    }
}
