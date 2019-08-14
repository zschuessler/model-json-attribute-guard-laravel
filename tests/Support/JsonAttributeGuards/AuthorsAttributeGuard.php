<?php

namespace Zschuessler\ModelJsonAttributeGuard\Test\Support\JsonAttributeGuards;

use Zschuessler\ModelJsonAttributeGuard\JsonAttributeGuard;

class AuthorsAttributeGuard extends JsonAttributeGuard
{
    public function schema() : array
    {
        return [
            '*.name' => 'required',
        ];
    }
}
