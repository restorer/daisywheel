<?php

namespace daisywheel\web;

use daisywheel\core\Component;

class Request extends Component
{
    public function fromGet($key, $def=null)
    {
        return (array_key_exists($key, $_GET) ? $_GET[$key] : $def);
    }

    public function fromPost($key, $def=null)
    {
        return (array_key_exists($key, $_POST) ? $_POST[$key] : $def);
    }

    // This function is not using $_REQUEST variable to be able to have consistent behavior
    // regardless of "request_order" value in php.ini
    public function fromRequest($key, $def=null)
    {
        return (array_key_exists($key, $_POST) ? $_POST[$key] : (array_key_exists($key, $_GET) ? $_GET[$key] : $def));
    }

    public function fromCookie($key, $def=null)
    {
        return (array_key_exists($key, $_COOKIE) ? $_COOKIE[$key] : $def);
    }

    public function fromServer($key, $def=null)
    {
        return (array_key_exists($key, $_SERVER) ? $_SERVER[$key] : $def);
    }

    public function fromEnv($key, $def=null)
    {
        return (array_key_exists($key, $_ENV) ? $_ENV[$key] : $def);
    }

    public function getContents()
    {
        return file_get_contents('php://input');
    }

    public function getMethod()
    {
        return $this->fromServer('REQUEST_METHOD');
    }

    public function getQueryString()
    {
        return $this->fromServer('QUERY_STRING');
    }

    public function getDocumentRoot()
    {
        return $this->fromServer('DOCUMENT_ROOT');
    }

    public function getHost()
    {
        return $this->fromServer('HTTP_HOST');
    }

    public function getReferer()
    {
        return $this->fromServer('HTTP_REFERER');
    }

    public function getUserAgent()
    {
        return $this->fromServer('HTTP_USER_AGENT');
    }

    public function getRemoteAddr()
    {
        return $this->fromServer('REMOTE_ADDR');
    }

    public function getRemoteHost()
    {
        return $this->fromServer('REMOTE_HOST');
    }

    public function getRemoteUser()
    {
        return $this->fromRequest('REMOTE_USER');
    }

    public function getRequestUri()
    {
        return $this->fromServer('REQUEST_URI');
    }

    public function getPathInfo()
    {
        return $this->fromServer('PATH_INFO');
    }

    public function isSecure()
    {
        $value = $this->fromServer('HTTPS', '');
        return ($value !== 'off' && $value != '');
    }
}
