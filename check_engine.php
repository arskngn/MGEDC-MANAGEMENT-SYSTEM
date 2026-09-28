<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$results = DB::select("SHOW TABLE STATUS WHERE Name IN ('product_batches', 'adjustment_items')");
foreach ($results as $r) {
    echo "Table: {$r->Name}, Engine: {$r->Engine}, Collation: {$r->Collation}\n";
}
