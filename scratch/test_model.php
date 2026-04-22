<?php
// Test model formatId
require 'server/vendor/autoload.php';

// Mocking CI4 environment just enough to test the method logic
class MockUserModel {
    protected function formatId(array $data)
    {
        if (isset($data['data'])) {
            // For multiple records, CI4 passes ['data' => [...results...]]
            // For single record, CI4 passes ['data' => ...result...]
            if (isset($data['data'][0]) && is_array($data['data'][0])) {
                // Multiple
                foreach ($data['data'] as &$row) {
                    if (isset($row['id'])) {
                        $row['_id'] = $row['id'];
                    }
                }
            } elseif (isset($data['data']['id'])) {
                // Single
                $data['data']['_id'] = $data['data']['id'];
            }
        }
        return $data;
    }
    
    public function test() {
        $multiple = ['data' => [['id' => 1, 'name' => 'Test']], 'singular' => false];
        $single = ['data' => ['id' => 2, 'name' => 'Single'], 'singular' => true];
        
        print_r($this->formatId($multiple));
        print_r($this->formatId($single));
    }
}

// Actual logic in UserModel.php:
/*
    protected function formatId(array $data)
    {
        if (isset($data['data'])) {
            if (isset($data['data']['id'])) {
                $data['data']['_id'] = $data['data']['id'];
            }
        } else {
            foreach ($data as &$row) {
                if (isset($row['id'])) {
                    $row['_id'] = $row['id'];
                }
            }
        }
        return $data;
    }
*/

$dataMultiple = ['data' => [['id' => 1, 'name' => 'Test']]];
// If isset($data['data']) is true, it only checks if isset($data['data']['id']).
// But $data['data'] is the array of rows. $data['data']['id'] is NOT set.
// So it does NOTHING for multiple rows.

echo "Testing existing logic:\n";
function existingFormatId(array $data)
{
    if (isset($data['data'])) {
        if (isset($data['data']['id'])) {
            $data['data']['_id'] = $data['data']['id'];
        }
    } else {
        foreach ($data as &$row) {
            if (isset($row['id'])) {
                $row['_id'] = $row['id'];
            }
        }
    }
    return $data;
}

print_r(existingFormatId($dataMultiple));
// Output will be same as input, no _id added.
