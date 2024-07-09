<?php

namespace app\adminapi;

class AuthBaseController
{
    protected array $adminInfo;
    protected int $adminId;
    protected array|string $adminRole;

    public function __construct()
    {
        $this->adminInfo = request()->adminInfo;
        $this->adminId = request()->adminId;
        $this->adminRole = request()->adminRole;
    }
}