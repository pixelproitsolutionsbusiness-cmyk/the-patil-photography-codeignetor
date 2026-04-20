<?php
$modelsDir = 'server/app/Models';
$files = scandir($modelsDir);

foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    
    $path = $modelsDir . '/' . $file;
    $content = file_get_contents($path);
    
    // Pattern to match existing formatId
    $pattern = '/protected function formatId\(array \$data\).*?\{.*?return \$data;.*?\}/s';
    
    $replacement = 'protected function formatId(array $data)
    {
        if (!isset($data[\'data\'])) {
            return $data;
        }

        if (isset($data[\'data\'][\'id\'])) {
            // Single result
            $data[\'data\'][\'_id\'] = $data[\'data\'][\'id\'];
        } else {
            // Multiple results
            foreach ($data[\'data\'] as &$row) {
                if (is_array($row) && isset($row[\'id\'])) {
                    $row[\'_id\'] = $row[\'id\'];
                } elseif (is_object($row) && isset($row->id)) {
                    $row->_id = $row->id;
                }
            }
        }
        return $data;
    }';

    if (preg_match($pattern, $content)) {
        $newContent = preg_replace($pattern, $replacement, $content);
        file_put_contents($path, $newContent);
        echo "Fixed $file\n";
    } else {
        echo "Skipped $file (pattern not found)\n";
    }
}
