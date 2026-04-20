<?php

namespace App\Models;

use CodeIgniter\Model;

class QuotationModel extends Model
{
    protected $table            = 'quotations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'quotationNumber', 'clientId', 'eventType', 'quotationDate', 'eventDate',
        'validityDate', 'subtotal', 'discount', 'discountType', 'taxPercentage',
        'tax', 'grandTotal', 'paymentTerms', 'notes', 'thankYouMessage', 'status',
        'clientName', 'email', 'whatsapp_no', 'location', 'retainerAmount',
        'stage', 'moodboard', 'channel', 'followUpDate', 'convertedToInvoice',
        'invoiceId', 'created_at', 'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowCallbacks = true;
    protected $afterFind      = ['formatId'];

<?php

namespace App\Models;

use CodeIgniter\Model;

class QuotationModel extends Model
{
    protected $table            = 'quotations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'quotationNumber', 'clientId', 'eventType', 'quotationDate', 'eventDate',
        'validityDate', 'subtotal', 'discount', 'discountType', 'taxPercentage',
        'tax', 'grandTotal', 'paymentTerms', 'notes', 'thankYouMessage', 'status',
        'clientName', 'email', 'whatsapp_no', 'location', 'retainerAmount',
        'stage', 'moodboard', 'channel', 'followUpDate', 'convertedToInvoice',
        'invoiceId', 'created_at', 'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowCallbacks = true;
    protected $afterFind      = ['formatId'];

    protected function formatId(array $data)
    {
        if (!isset($data['data'])) {
            return $data;
        }

        $formatRow = function (&$row) {
            if (is_array($row)) {
                if (isset($row['id'])) $row['_id'] = $row['id'];
                if (isset($row['created_at'])) $row['createdAt'] = $row['created_at'];
                if (isset($row['updated_at'])) $row['updatedAt'] = $row['updated_at'];
            } elseif (is_object($row)) {
                if (isset($row->id)) $row->_id = $row->id;
                if (isset($row->created_at)) $row->createdAt = $row->created_at;
                if (isset($row->updated_at)) $row->updatedAt = $row->updated_at;
            }
        };

        if (isset($data['data']['id']) || (isset($data['singular']) && $data['singular'])) {
            $formatRow($data['data']);
        } else {
            foreach ($data['data'] as &$row) {
                $formatRow($row);
            }
        }
        return $data;
    }
}