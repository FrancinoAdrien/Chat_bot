<?php

namespace App\Tools;

class GetLowStockTool extends BaseTool
{
    public function endpoint(): string
    {
        return '/api/ai/low-stock';
    }
}
