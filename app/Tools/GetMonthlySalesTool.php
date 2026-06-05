<?php

namespace App\Tools;

class GetMonthlySalesTool extends BaseTool
{
    public function endpoint(): string
    {
        return '/api/ai/sales/month';
    }
}
