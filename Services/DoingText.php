<?php
/**
 * @category Services
 *
 * @author Till Klampaeckel <till@php.net>
 * @license New BSD License
 */
class Services_DoingText
{
    protected $endpoint = 'http://doingtext.com/';
    protected $httpClient;
    protected $password;
    protected $username;

    public function __construct($username, $password, HTTP_Request2 $httpClient = null)
    {
        $this->username = $username;
        $this->password = $password;
        
        if ($client === null) {
            
        } else {
            $this->httpClient = $httpClient;
            $this->httpClient->setAuth($username, $password);
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
    protected makeRequest($url, $method = 'GET')
    {
        $uri = $this->endpoint . $url;
        $this->httpClient->setUrl($uri);

        $this->httpClient->setMethod($method);
        return $this->httpClient->send();
    }

    /**
     * Parse the response from {@link makeRequest()}.
     *
     * @param string $xml XML'd response.
     *
     * @return string
     * @todo   Throw exception if something is wrong!
     */
    protected function parseResponse($xml)
    {
        return simple_xml_loadstring($xml);
    }
}