<?php

namespace app\Service\Auth;

use think\Request;

class ParseAuthorization
{
    protected $authorization;

    /** @var string|null */
    protected $token;

    /** @var string|null */
    protected $machine;

    public function __construct(Request $request)
    {
        $this->authorization = $request->header('authorization');
        $this->parse();
    }

    protected function parse()
    {
        if (empty($this->authorization)) {
            return;
        }
        if (!str_starts_with($this->authorization, 'Bearer')) {
            return;
        }

        if (!preg_match_all('/(\S+)="(\S+?)"/', $this->authorization, $matches, PREG_SET_ORDER)) {
            return;
        }

        foreach ($matches as $match) {
            switch ($match[1]) {
                case 'TK':
                    $this->token = $match[2];
                    break;
                case 'MC':
                    $this->machine = $match[2];
                    break;
            }
        }
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @return string|null
     */
    public function getMachine(): ?string
    {
        return $this->machine;
    }
}
