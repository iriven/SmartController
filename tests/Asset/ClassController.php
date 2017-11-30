<?php

namespace Xocotlah\SmartController\Tests\Asset;
use Xocotlah\SmartController\Tests\Asset\stringableClass;

class ClassController
{

    public function stringAction($request, $response)
    {
        return 'Hello world';
    }

    public function arrayAction($request, $response)
    {
        return ['Hello' => 'world'];
    }

    public function stringableAction($request, $response)
    {
        return new stringableClass('Hello world');
    }
}
