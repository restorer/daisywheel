<?php

namespace daisywheel\web;

use daisywheel\core\Component;

class Response extends Component
{
    const MIME_ATOM = 'application/atom+xml';
    const MIME_JSON = 'application/json';
    const MIME_BINARY = 'application/octet-stream';
    const MIME_PDF = 'application/pdf';
    const MIME_RDF = 'application/rdf+xml';
    const MIME_RSS = 'application/rss+xml';
    const MIME_XML = 'application/xml';
    const MIME_ZIP = 'application/zip';
    const MIME_GZIP = 'application/gzip';

    const MIME_GIF = 'image/gif';
    const MIME_JPEG = 'image/jpeg';
    const MIME_PNG = 'image/png';
    const MIME_SVG = 'image/svg+xml';

    const MIME_CSS = 'text/css';
    const MIME_CSV = 'text/csv';
    const MIME_HTML = 'text/html';
    const MIME_JAVASCRIPT = 'text/javascript';
    const MIME_TEXT = 'text/plain';
    const MIME_RTF = 'text/rtf';

    const MIME_7Z = 'application/x-7z-compressed';
    const MIME_CHROME_EXTENSION = 'application/x-chrome-extension';
    const MIME_RAR = 'application/x-rar-compressed';
    const MIME_TAR = 'application/x-tar';

    const MIME_ODT = 'application/vnd.oasis.opendocument.text';
    const MIME_ODS = 'application/vnd.oasis.opendocument.spreadsheet';
    const MIME_ODP = 'application/vnd.oasis.opendocument.presentation';
    const MIME_ODG = 'application/vnd.oasis.opendocument.graphics';
    const MIME_XLS = 'application/vnd.ms-excel';
    const MIME_XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    const MIME_PPT = 'application/vnd.ms-powerpoint';
    const MIME_PPTX = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    const MIME_DOC = 'application/msword';
    const MIME_DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    const MIME_KML = 'application/vnd.google-earth.kml+xml';
    const MIME_KMZ = 'application/vnd.google-earth.kmz';
    const MIME_APK = 'application/vnd.android.package-archive';

    const MIME_AUDIO_MP4 = 'audio/mp4';
    const MIME_AUDIO_MPEG = 'audio/mpeg';
    const MIME_AUDIO_OGG = 'audio/ogg';
    const MIME_AUDIO_WAV = 'audio/vnd.wave';
    const MIME_AUDIO_WEBM = 'audio/webm';

    const MIME_VIDEO_AVI = 'video/avi';
    const MIME_VIDEO_MPEG = 'video/mpeg';
    const MIME_VIDEO_MP4 = 'video/mp4';
    const MIME_VIDEO_OGG = 'video/ogg';
    const MIME_VIDEO_QUICKTIME = 'video/quicktime';
    const MIME_VIDEO_WEBM = 'video/webm';
    const MIME_VIDEO_MATROSKA = 'video/x-matroska';

    const HEADER_CONTENT_TYPE = 'Content-type';

    protected $headers = array();
    protected $cookies = array();

    public function init($config)
    {
        ob_start();
        $this->headers[self::HEADER_CONTENT_TYPE] = self::MIME_HTML;
    }

    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function getHeader($key, $def=null)
    {
        return (array_key_exists($key, $this->headers) ? $this->headers[$key] : $def);
    }

    public function setContentType($contentType)
    {
        return $this->setHeader(self::HEADER_CONTENT_TYPE, $contentType);
    }

    public function getContentType()
    {
        return $this->getHeader(self::HEADER_CONTENT_TYPE);
    }

    public function setBuffer($str)
    {
        ob_clean();
        print_r($str);

        return $this;
    }

    public function getBuffer()
    {
        return ob_get_contents();
    }

    public function append($str)
    {
        print_r($str);
        return $this;
    }

    public function dump($str)
    {
        var_dump($str);
        return $this;
    }

    public function setCookie($key, $value, $expire=0, $path='/', $domain='', $secure=false, $httpOnly=false)
    {
        $this->cookies[$key] = array(
            'value' => $value,
            'expire' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httpOnly' => $httpOnly,
        );
    }

    public function deleteCookie($key, $path='/', $domain='', $secure=false)
    {
        $this->cookies[$key] = array(
            'value' => '',
            'expire' => time() - 3600,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httpOnly' => false,
        );
    }

    public function flush()
    {
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }

        // IE7 can have trouble with settings cookies that are embedded in an iframe.
        // The problem lies with a W3C standard called Platform for Privacy Preferences or P3P for short.
        // To overcome, include following header before setting the cookie:
        // header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

        foreach ($this->cookies as $key => $cookie) {
            setcookie($key, $cookie['value'], $cookie['expire'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httpOnly']);
        }

        ob_end_flush();
        flush();

        return $this;
    }
}
