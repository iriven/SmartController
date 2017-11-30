<?php

namespace Xocotlah\SmartController\Tests\Asset;

class stringableClass
{
    protected $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function __toString()
    {
        return $this->content;
    }
}
