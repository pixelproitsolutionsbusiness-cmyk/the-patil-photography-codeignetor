<?php

namespace App\Models;

use CodeIgniter\Model;

class QuotationItemModel extends Model
{
    protected $table            = 'quotation_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'quotation_id', 'service_id', 'serviceName', 'quantity', 'days', 'ratePerDay', 'total'
    ];
}
