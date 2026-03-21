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
}
