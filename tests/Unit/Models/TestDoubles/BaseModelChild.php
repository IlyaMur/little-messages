<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Tests\Unit\Models\TestDoubles;

use Ilyamur\PhpMvc\Models\BaseModel;

class BaseModelChild extends BaseModel
{
    public static function getDB()
    {
        return parent::getDB();
    }
}
