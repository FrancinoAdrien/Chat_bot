<?php

namespace App\Tools;

class GetSalesTodayTool extends BaseTool
{
    public function endpoint(): string
    {
        return '/api/ai/sales/today';
    }
}
