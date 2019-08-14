<?php

namespace Zschuessler\ModelJsonAttributeGuard\Test\Integration;

use Zschuessler\ModelJsonAttributeGuard\Test\Support\Models\ModelWithTrait;
use Zschuessler\ModelJsonAttributeGuard\Test\TestCase;

class TraitTest extends TestCase
{
    /**
     * @test
     */
    public function default_configuration_throws_no_exception()
    {
        $defaultModel = new ModelWithTrait();

        $defaultModel->title = 'Good to Great';
        $defaultModel->authors = [
            [
                'name' => 'Jim Collins'
            ]
        ];
        $defaultModel->notes = 'test';
        $defaultModel->save();

        $this->assertTrue(
            $defaultModel->exists
        );
    }
}
