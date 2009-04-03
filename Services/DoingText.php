<?php

/**
 * Services_DoingText_Exception
 */
require_once 'Services/DoingText/Exception.php';

/**
 * @category Services
 *
 * @author Till Klampaeckel <till@php.net>
 * @license New BSD License
 * @todo Services_DoingText::setClient()
 * @todo Services_DoingText::setAuth()
 * @todo Services_DoingText::setEndpoint()
 */
class Services_DoingText
{
    protected $endpoint = 'http://doingtext.com/';
    protected $httpClient;
    protected $password;
    protected $username;

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
                throw new Services_DoingText_Exception('Could not load HTTP_Request2.', 500);
            }
            $this->httpClient = new HTTP_Request2();
        } else {
            $this->httpClient = $httpClient;
        }
        $this->httpClient->setAuth($username, $password);
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
        $data               = array();
        $data['discussion'] = array();

        if ($title !== null) {
            $data['discussion']['title'] = $title;
        }
        if ($permaLink === null) {
            $data['discussion']['permaLink'] = $permaLink;
        }
        $data['discussion']['content'] = $content;
        
        $this->httpClient->addPostParameter($data);
        
        $response = $this->makeRequest('discussions.xml', 'POST');
        
        $obj = $this->parseResponse($response);
        return $obj;
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
        $obj = $this->parseResponse($response);
        return $obj;
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
        $uri = $this->endpoint . $url;
        
        $this->httpClient->setUrl($uri);

        $this->httpClient->setMethod($method);
        $response = $this->httpClient->send();
        
        if (!in_array($response->getStatus(), array(200, 304))) {
            switch ($response->getStatus()) {
            case 401:
                $msg = 'Wrong username and/or password';
                break;
            default:
                $msg = "An error occured: {$response->getStatus()}";
                break;
            }
            throw new Services_DoingText_Exception($msg, $response->getStatus());
        }
        
        return $response->getBody();
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