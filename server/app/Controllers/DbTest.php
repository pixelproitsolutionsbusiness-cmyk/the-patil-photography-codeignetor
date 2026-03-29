<?php
namespace App\Controllers;
use CodeIgniter\Controller;

class DbTest extends Controller {
    public function index() {
        try {
            $db = \Config\Database::connect();
            $query = $db->query("DESCRIBE users");
            $schema = array_map(function($row) { return (array)$row; }, $query->getResult());
            return "Connected! Schema: " . print_r($schema, true);
        } catch (\Exception $e) {
            return "Failed! " . $e->getMessage();
        }
    }
}
