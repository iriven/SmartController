<?php

namespace Xocotlah\SmartController\Tests\Asset;

use Xocotlah\SmartController\SmartController;

class ExtendsSmartController extends SmartController
{
    public function arrayAction()
    {
        return ['Hello' => $this->params['param']];
    }

    public function stringAction()
    {
        return 'Hello '.$this->params['param'];
    }

    public function stringableAction()
    {
        return new StringableClass('Hello '.$this->params['param']);
    }

}
