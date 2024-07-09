<?php

namespace app\api\controller;

use app\Request;
use exceptions\ApiException;
use think\App;

class Index
{

    protected Request $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index()
    {
        [$id, $name, $a] = $this->request->getMore([
            ['id'],
            ['name'],
            ['a']
        ]);
        return app('json')->fail(throw new ApiException(410025));
    }
}
