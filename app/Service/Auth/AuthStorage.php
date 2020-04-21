<?php

namespace app\Service\Auth;

class AuthStorage
{
    public function __construct(array $data)
    {
        $this->features = $data['features'];
        $this->permission = $data['permission'];
        $this->pe2fe = $data['permission2features'] ?? [];
        $this->fe2pe = $data['features2permission'] ?? [];
    }

    public $features = [];
    public $permission = [];
    public $pe2fe = [];
    public $fe2pe = [];

    public function toArray()
    {
        return [
            'features' => $this->features,
            'permission' => $this->permission,
            'permission2features' => $this->pe2fe,
            'features2permission' => $this->fe2pe,
        ];
    }
}
