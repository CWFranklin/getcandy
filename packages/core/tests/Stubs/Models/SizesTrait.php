<?php

namespace GetCandy\Tests\Stubs\Models;

use Illuminate\Support\Collection;

trait SizesTrait
{
    public function extendedSizes(): Collection
    {
        return collect(['xl', 'xxl']);
    }
}
