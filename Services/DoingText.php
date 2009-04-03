<?php
/** 
 * +-----------------------------------------------------------------------+
 * | Copyright (c) 2009, Till Klampaeckel                                  |
 * | All rights reserved.                                                  |
 * |                                                                       |
 * | Redistribution and use in source and binary forms, with or without    |
 * | modification, are permitted provided that the following conditions    |
 * | are met:                                                              |
 * |                                                                       |
 * | o Redistributions of source code must retain the above copyright      |
 * |   notice, this list of conditions and the following disclaimer.       |
 * | o Redistributions in binary form must reproduce the above copyright   |
 * |   notice, this list of conditions and the following disclaimer in the |
 * |   documentation and/or other materials provided with the distribution.|
 * | o The names of the authors may not be used to endorse or promote      |
 * |   products derived from this software without specific prior written  |
 * |   permission.                                                         |
 * |                                                                       |
 * | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
 * | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
 * | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
 * | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
 * | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
 * | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
 * | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
 * | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
 * | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
 * | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
 * | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
 * |                                                                       |
 * +-----------------------------------------------------------------------+
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_DoingText
 * @author   Till Klampaeckel <till@php.net>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  GIT: ID
 * @link     http://github.com/till/services_doingtext/tree/master
 */

/**
 * Services_DoingText_Exception
 */
require_once 'Services/DoingText/Exception.php';

/**
 * Services_DoingText_Response
 */
require_once 'Services/DoingText/Response.php';

/**
 * Services_DoingText
 *
 * @category Services
 * @package  Services_DoingText
 * @author   Till Klampaeckel <till@php.net>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://github.com/till/services_doingtext/tree/master
 * @todo     Services_DoingText::setClient()
 * @todo     Services_DoingText::setAuth()
 * @todo     Services_DoingText::setEndpoint()
 */
class Services_DoingText
{
    protected $endpoint = 'http://doingtext.com/';
    protected $httpClient;
    protected $password;
    protected $username;
    
    protected $responseClass = 'Services_DoingText_Response';
    
    /**
     * Error/Status codes, conform to HTTP.
     * @access global
     */
    const ERR_NOT_ACCEPTABLE = 406;
    const ERR_PRECONDITION   = 412;
    /**
     * __construct
     *
     * @param string        $username   The username (or email).
     * @param string        $password   The password.
     * @param HTTP_Request2 $httpClient Optional, in case you need the customization.
     *
     * @returns Services_DoingText
     */
    public function __construct($username, $password, HTTP_Request2 $httpClient = null)
    {
        $this->username = $username;
        $this->password = $password;
        
        if ($httpClient === null) {
            if (!class_exists('HTTP_Request2')) {
                include_once 'HTTP/Request2.php';
            }
            if (!class_exists('HTTP_Request2')) {
                throw new Services_DoingText_Exception(
                    'Could not load HTTP_Request2.',
                    self::ERR_PRECONDITION
                );
            }
            $this->httpClient = new HTTP_Request2();
            $this->httpClient->setAuth($this->username, $this->password);
        } else {
            $this->httpClient = $httpClient;
            $auth = $this->httpClient->getAuth();
            if (!isset($auth['username']) || empty($auth['username'])) {
                $this->httpClient->setAuth($this->username, $this->password);
            }
        }
    }

    /**
     * Create a new discussion on doingText.
     *
     * @param string $content   The content of the discussion.
     * @param string $title     Optional, the name of the discussion.
     * @param string $permaLink The permalink, optional.
     *
     * @return object
     */
    public function add($content, $title = null, $permaLink = null)
    {
        $data = array();

        if ($title !== null) {
            $data['title'] = $title;
        }
        if ($permaLink !== null) {
            $data['permalink'] = $permaLink;
        }
        $data['content'] = $content;
        
        $this->httpClient->addPostParameter(array('discussion' => $data));

        $response = $this->makeRequest('discussions.xml', 'POST');

        $obj = $this->parseResponse($response['body']);
        
        $cls = new $this->responseClass($obj);
        $cls->setCode($response['code']);
        $cls->setBody($response['body']);

        return $cls;
    }

    /**
     * If no parameter is provided, we get all discussions of the current user.
     *
     * @param string $permaLink Optional, the permalink/ID of a discussion.
     *
     * @return array
     * @uses   self::makeRequest()
     * @uses   self::parseResponse()
     */
    public function get($permaLink = null)
    {
        if ($permaLink === null) {
            $response = $this->makeRequest('profile.xml');
        } else {
            $response = $this->makeRequest("discussions/{$permaLink}.xml");
        }

        $obj = $this->parseResponse($response['body']);
        
        $cls = new $this->responseClass($obj);
        $cls->setCode($response['code']);
        $cls->setBody($response['body']);
        
        return $cls;
    }

    /**
     * In case you want to override the response. This class must extend
     * Services_DoingText_Response
     *
     * @param string $className The class to use.
     *
     * @return Services_DoingText
     * @throws Services_DoingText_Exception If the class is not loaded!
     */
    public function setResponseClass($className)
    {
        if (!class_exists($className)) {
            throw new Services_DoingText_Exception(
                "Class {$className} not found",
                self::ERR_PRECONDITION
            );
        }
        $this->responseClass = $className;
        return $this;
    }

    /**
     * Make a request using {@link self::$httpClient}.
     *
     * @param string $url    part of the URL.
     * @param string $method GET, or POST.
     *
     * @return string
     * @uses   self::$endpoint
     * @uses   self::$httpClient
     */
    protected function makeRequest($url, $method = 'GET')
    {
        $method = strtoupper($method);

        switch($method) {
        case 'GET':
        case 'POST':
        case 'PUT':
            break;
        default:
            throw new Services_DoingText_Exception(
                "Unsupported method {$method} used.",
                self::ERR_PRECONDITION
            );
        }
        $uri = $this->endpoint . $url;

        $this->httpClient->setUrl($uri);

        $this->httpClient->setMethod($method);
        $response = $this->httpClient->send();
        $status   = $response->getStatus();
        $body     = $response->getBody();
        
        if (!in_array($status, array(200, 201, 302, 304))) {
            switch ($status) {
            case 401:
                $msg = 'Wrong username and/or password';
                break;
            case 422:
                $msg = "Provided input parameters are invalid.";
                break;
            default:
                $msg  = "An error occured: {$status}\n";
                $msg .= "Message: {$body}\n";
                $msg .= "Header:\n";
                foreach ($response->getHeader() as $header => $value) {
                    $msg .= "{$header}: {$value}\n";
                }
                break;
            }
            throw new Services_DoingText_Exception($msg, $status);
        }
        if ($status == 302) {
            $location = $response->getHeader('location');
            if ($location === null) {
                throw new Services_DoingText_Exception(
                    'Server sent a notice to redirect, but provided no url to follow.',
                    self::ERR_PRECONDITION
                );
            }
            $location = str_replace($this->endpoint, '', $location); // strip endpoint
            return $this->makeRequest($location);
        } else {
            return array(
                'body' => $body,
                'code' => $status,
            );
        }
    }

    /**
     * Parse the response from {@link makeRequest()}. Forces an array and we 
     * receive an array stacked with SimpleXMLElements.
     *
     * @param string $xml XML'd response.
     *
     * @return array
     */
    protected function parseResponse($xml)
    {   
        return (array) simplexml_load_string($xml);
    }
}