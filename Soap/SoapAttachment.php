<?php

namespace Bundle\WebServiceBundle\Soap;

class SoapAttachment
{
    private $id;
    private $type;
    private $content;

    public function __construct($id, $type, $content)
    {
        $this->id = $id;
        $this->type = $type;
        $this->content = $content;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getContent()
    {
        return $this->content;
    }
}