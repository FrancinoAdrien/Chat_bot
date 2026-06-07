<?php
$tools = App\Models\Tool::all();
foreach ($tools as $t) {
    echo "{$t->id} - {$t->name} ({$t->label}) : {$t->endpoint}\n";
}
