<?php

namespace App\Tools;

class GetTopProductsTool extends BaseTool
{
    public function endpoint(): string
    {
        return '/api/ai/top-products';
    }
}
