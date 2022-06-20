<?php
/**
 * SandboxApi
 * PHP version 5
 *
 * @category Class
 * @package  idcheckio
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */

/**
 * IdCheck.IO API
 *
 * Check identity documents
 *
 * OpenAPI spec version: 0.0
 *
 * Generated by: https://github.com/swagger-api/swagger-codegen.git
 *
 */

/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace idcheckio\api;

use idcheckio\ApiClient;
use idcheckio\ApiException;
use idcheckio\model\ImageListResponse;
use idcheckio\model\MrzListResponse;
use idcheckio\model\MrzResponse;

/**
 * SandboxApi Class Doc Comment
 *
 * @category Class
 * @package  idcheckio
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */
class SandboxApi
{
    /**
     * API Client
     *
     * @var ApiClient instance of the ApiClient
     */
    protected $apiClient;

    /**
     * Constructor
     *
     * @param ApiClient|null $apiClient The api client to use
     */
    public function __construct(ApiClient $apiClient = null)
    {
        if ($apiClient === null) {
            $apiClient = new ApiClient();
        }

        $this->apiClient = $apiClient;
    }

    /**
     * Get API client
     *
     * @return ApiClient get the API client
     */
    public function getApiClient()
    {
        return $this->apiClient;
    }

    /**
     * Set the API client
     *
     * @param ApiClient $apiClient set the API client
     *
     * @return SandboxApi
     */
    public function setApiClient(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
        return $this;
    }

    /**
     * Operation getImage
     *
     * HTTP GET image
     *
     * @param string $image_uid EnumDemoDocsImage (required)
     * @param string $raw_type Image raw type (optional)
     * @param string $face Image face (optional)
     * @param string $light Image light (optional)
     * @return string[]
     *@throws ApiException on non-2xx response
     */
    public function getImage($image_uid, $raw_type = null, $face = null, $light = null)
    {
        [$response] = $this->getImageWithHttpInfo($image_uid, $raw_type, $face, $light);
        return $response;
    }

    /**
     * Operation getImageWithHttpInfo
     *
     * HTTP GET image
     *
     * @param string $image_uid EnumDemoDocsImage (required)
     * @param string $raw_type Image raw type (optional)
     * @param string $face Image face (optional)
     * @param string $light Image light (optional)
     * @return array of string[], HTTP status code, HTTP response headers (array of strings)
     *@throws ApiException on non-2xx response
     */
    public function getImageWithHttpInfo($image_uid, $raw_type = null, $face = null, $light = null)
    {
        // verify the required parameter 'image_uid' is set
        if ($image_uid === null) {
            throw new \InvalidArgumentException('Missing the required parameter $image_uid when calling getImage');
        }
        // parse inputs
        $resourcePath = "/v0/sandbox/image/{imageUid}";
        $httpBody = '';
        $queryParams = [];
        $headerParams = [];
        $formParams = [];
        $_header_accept = $this->apiClient->selectHeaderAccept(['*/*']);
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType([]);

        // query params
        if ($raw_type !== null) {
            $queryParams['rawType'] = $this->apiClient->getSerializer()->toQueryValue($raw_type);
        }
        // query params
        if ($face !== null) {
            $queryParams['face'] = $this->apiClient->getSerializer()->toQueryValue($face);
        }
        // query params
        if ($light !== null) {
            $queryParams['light'] = $this->apiClient->getSerializer()->toQueryValue($light);
        }
        // path params
        if ($image_uid !== null) {
            $resourcePath = str_replace(
                "{" . "imageUid" . "}",
                $this->apiClient->getSerializer()->toPathValue($image_uid),
                $resourcePath
            );
        }
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);


        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        // make the API Call
        try {
            [$response, $statusCode, $httpHeader] = $this->apiClient->callApi(
                $resourcePath,
                'GET',
                $queryParams,
                $httpBody,
                $headerParams,
                'string[]',
                '/v0/sandbox/image/{imageUid}'
            );

            return [$this->apiClient->getSerializer()->deserialize($response, 'string[]', $httpHeader), $statusCode, $httpHeader];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), 'string[]', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 404:
                case 500:
                case 503:
                case 400:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\idcheckio\model\ErrorResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }

    /**
     * Operation getImageList
     *
     * HTTP GET images list
     *
     * @throws ApiException on non-2xx response
     * @return ImageListResponse
     */
    public function getImageList()
    {
        [$response] = $this->getImageListWithHttpInfo();
        return $response;
    }

    /**
     * Operation getImageListWithHttpInfo
     *
     * HTTP GET images list
     *
     * @return array of \idcheckio\model\ImageListResponse, HTTP status code, HTTP response headers (array of strings)
     *@throws ApiException on non-2xx response
     */
    public function getImageListWithHttpInfo()
    {
        // parse inputs
        $resourcePath = "/v0/sandbox/imagelist";
        $httpBody = '';
        $queryParams = [];
        $headerParams = [];
        $formParams = [];
        $_header_accept = $this->apiClient->selectHeaderAccept(['application/json; charset=utf-8']);
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType([]);

        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);


        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        // make the API Call
        try {
            [$response, $statusCode, $httpHeader] = $this->apiClient->callApi(
                $resourcePath,
                'GET',
                $queryParams,
                $httpBody,
                $headerParams,
                '\idcheckio\model\ImageListResponse',
                '/v0/sandbox/imagelist'
            );

            return [$this->apiClient->getSerializer()->deserialize($response, '\idcheckio\model\ImageListResponse', $httpHeader), $statusCode, $httpHeader];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\idcheckio\model\ImageListResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 500:
                case 503:
                case 404:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\idcheckio\model\ErrorResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }

    /**
     * Operation getMrz
     *
     * HTTP GET mrz
     *
     * @param string $mrz_uid EnumDemoDocsMrz (required)
     * @return MrzResponse
     *@throws ApiException on non-2xx response
     */
    public function getMrz($mrz_uid)
    {
        [$response] = $this->getMrzWithHttpInfo($mrz_uid);
        return $response;
    }

    /**
     * Operation getMrzWithHttpInfo
     *
     * HTTP GET mrz
     *
     * @param string $mrz_uid EnumDemoDocsMrz (required)
     * @return array of \idcheckio\model\MrzResponse, HTTP status code, HTTP response headers (array of strings)
     *@throws ApiException on non-2xx response
     */
    public function getMrzWithHttpInfo($mrz_uid)
    {
        // verify the required parameter 'mrz_uid' is set
        if ($mrz_uid === null) {
            throw new \InvalidArgumentException('Missing the required parameter $mrz_uid when calling getMrz');
        }
        // parse inputs
        $resourcePath = "/v0/sandbox/mrz/{mrzUid}";
        $httpBody = '';
        $queryParams = [];
        $headerParams = [];
        $formParams = [];
        $_header_accept = $this->apiClient->selectHeaderAccept(['application/json; charset=utf-8']);
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType([]);

        // path params
        if ($mrz_uid !== null) {
            $resourcePath = str_replace(
                "{" . "mrzUid" . "}",
                $this->apiClient->getSerializer()->toPathValue($mrz_uid),
                $resourcePath
            );
        }
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);


        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        // make the API Call
        try {
            [$response, $statusCode, $httpHeader] = $this->apiClient->callApi(
                $resourcePath,
                'GET',
                $queryParams,
                $httpBody,
                $headerParams,
                '\idcheckio\model\MrzResponse',
                '/v0/sandbox/mrz/{mrzUid}'
            );

            return [$this->apiClient->getSerializer()->deserialize($response, '\idcheckio\model\MrzResponse', $httpHeader), $statusCode, $httpHeader];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\idcheckio\model\MrzResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 404:
                case 500:
                case 503:
                case 400:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\idcheckio\model\ErrorResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }

    /**
     * Operation getMrzList
     *
     * HTTP GET mrz list
     *
     * @return MrzListResponse
     *@throws ApiException on non-2xx response
     */
    public function getMrzList()
    {
        [$response] = $this->getMrzListWithHttpInfo();
        return $response;
    }

    /**
     * Operation getMrzListWithHttpInfo
     *
     * HTTP GET mrz list
     *
     * @return array of \idcheckio\model\MrzListResponse, HTTP status code, HTTP response headers (array of strings)
     *@throws ApiException on non-2xx response
     */
    public function getMrzListWithHttpInfo()
    {
        // parse inputs
        $resourcePath = "/v0/sandbox/mrzlist";
        $httpBody = '';
        $queryParams = [];
        $headerParams = [];
        $formParams = [];
        $_header_accept = $this->apiClient->selectHeaderAccept(['application/json; charset=utf-8']);
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType([]);

        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);


        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        // make the API Call
        try {
            [$response, $statusCode, $httpHeader] = $this->apiClient->callApi(
                $resourcePath,
                'GET',
                $queryParams,
                $httpBody,
                $headerParams,
                '\idcheckio\model\MrzListResponse',
                '/v0/sandbox/mrzlist'
            );

            return [$this->apiClient->getSerializer()->deserialize($response, '\idcheckio\model\MrzListResponse', $httpHeader), $statusCode, $httpHeader];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\idcheckio\model\MrzListResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 500:
                case 503:
                case 404:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\idcheckio\model\ErrorResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }
}
