<?php

namespace Perfumer\Proxy;

class Response
{
    /**
     * Response status codes translation table.
     *
     * @var array
     */
    protected $status_messages = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Reserved for WebDAV advanced collections expired proposal',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    ];

    /**
     * Response protocol version.
     *
     * @var string
     */
    protected $protocol_version = '1.0';

    /**
     * Response status code.
     *
     * @var int
     */
    protected $status_code = 200;

    protected $headers = [];
    protected $body;

    public function __construct()
    {
        if ('HTTP/1.0' != $_SERVER['SERVER_PROTOCOL'])
            $this->protocol_version = '1.1';
    }

    public function getProtocolVersion()
    {
        return $this->protocol_version;
    }

    public function setProtocolVersion($version)
    {
        if (in_array($version, ['1.0', '1.1']))
            $this->protocol_version = $version;

        return $this;
    }

    public function getStatusCode()
    {
        return $this->status_code;
    }

    public function setStatusCode($code)
    {
        if (isset($this->status_messages[$code]))
            $this->status_code = $code;

        return $this;
    }

    public function getStatusMessage($code, $default = null)
    {
        return isset($this->status_messages[$code]) ? $this->status_messages[$code] : $default;
    }

    public function sendHeaders()
    {
        if (headers_sent())
            return $this;

        header(sprintf('HTTP/%s %s %s', $this->protocol_version, $this->status_code, $this->status_messages[$this->status_code]));

        foreach ($this->headers as $name => $value)
            header($name . ': ' . $value);

        return $this;
    }

    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function hasHeader($name)
    {
        return isset($this->headers[$name]);
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }
}