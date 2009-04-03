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
class Services_DoingText_Response extends ArrayObject
{
    protected $code;
    protected $body;

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }
    
    public function __call($name, $args)
    {
        $func = strtolower($name);
        switch ($func) {
        case 'getbody':
            return $this->body;
        case 'getcode':
        case 'getstatus':
            return $this->code;
        default:
            throw new Services_DoingText_Exception(
                "Method {$name} not trapped in call.",
                Services_DoingText::ERR_NOT_ACCEPTABLE
            );
        }
    }
}