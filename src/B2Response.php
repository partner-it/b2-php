<?php
namespace B2;

/**
 * Class B2Response
 * @package B2
 */
class B2Response
{

    private $data = null;

    private $statusCode = 0;

    private $headers = [];

    public function __construct()
    {
    }

    /**
     * @param $header
     */
    public function addHeader($header)
    {
        $this->headers[] = $header;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getJsonData()
    {
        if ($this->data) {
            return json_decode($this->data, true);
        }
    }

    public function getData()
    {
        return $this->data;
    }

}

