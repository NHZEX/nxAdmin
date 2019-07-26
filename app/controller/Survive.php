<?php
declare(strict_types=1);

namespace app\controller;

use think\Response;

class Survive extends Base
{
    public function index()
    {
        $table = [
            ['Time', time()],
            ['DB Main', 'null'],
            ['Redis', 'null'],
        ];

        $cellWidthMax = array_reduce($table, function ($carry, $item) {
            return max($carry, mb_strwidth($item[0]));
        }, 0);
        $cellWidthMax += 4;

        foreach ($table as &$row) {
            $row[0] = str_pad($row[0], $cellWidthMax, ' ', STR_PAD_RIGHT);
        }

        $result = PHP_EOL;
        foreach ($table as $row) {
            $result .= join(': ', $row) . PHP_EOL;
        }

        return Response::create($result, '', 200);
    }
}
