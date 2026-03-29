<?php

$modelsDir = __DIR__ . '/app/Models';
$models = glob($modelsDir . '/*.php');

$callbackCode = <<<EOD
    protected \$allowCallbacks = true;
    protected \$afterFind      = ['formatId'];

    protected function formatId(array \$data)
    {
        if (isset(\$data['data'])) {
            if (isset(\$data['data']['id'])) {
                \$data['data']['_id'] = \$data['data']['id'];
            }
        } else {
            foreach (\$data as &\$row) {
                if (isset(\$row['id'])) {
                    \$row['_id'] = \$row['id'];
                }
            }
        }
        return \$data;
    }
}
EOD;

foreach ($models as $modelFile) {
    if (basename($modelFile) === 'BaseModel.php' || basename($modelFile) === 'InvoiceItemModel.php') continue;
    
    $content = file_get_contents($modelFile);
    
    // Check if formatId already exists
    if (strpos($content, 'formatId') !== false) {
        echo "Skipping " . basename($modelFile) . " (already has formatId)\n";
        continue;
    }
    
    // Check if it's a class ending with }
    if (preg_match('/}\s*$/', $content)) {
        $content = preg_replace('/}\s*$/', $callbackCode, $content);
        file_put_contents($modelFile, $content);
        echo "Updated " . basename($modelFile) . "\n";
    }
}
