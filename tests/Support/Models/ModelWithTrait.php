<?php

namespace Zschuessler\ModelJsonAttributeGuard\Test\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Zschuessler\ModelJsonAttributeGuard\Test\Support\JsonAttributeGuards\AuthorsAttributeGuard;
use Zschuessler\ModelJsonAttributeGuard\Traits\HasJsonAttributeGuards;

class ModelWithTrait extends Model
{
    use HasJsonAttributeGuards;

    public $table = 'zacs_favorite_books';

    public $casts = [
        'authors' => AuthorsAttributeGuard::class,
        'notes'   => 'object'
    ];
}
