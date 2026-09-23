<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$rows = DB::select(
    'SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY '
    .'FROM information_schema.columns '
    .'WHERE table_schema = DATABASE() '
    .'ORDER BY TABLE_NAME, ORDINAL_POSITION'
);

$by = [];
foreach ($rows as $r) {
    $by[$r->TABLE_NAME][] = $r;
}

foreach ($by as $table => $cols) {
    echo "\n# {$table}\n";
    foreach ($cols as $c) {
        echo "- {$c->COLUMN_NAME} ({$c->COLUMN_TYPE})\n";
    }
}
