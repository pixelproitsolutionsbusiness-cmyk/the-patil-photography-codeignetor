<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table            = 'invoices';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'invoiceNumber', 'clientId', 'quotationId', 'eventType', 'invoiceDate', 
        'eventDate', 'dueDate', 'subtotal', 'discount', 'discountType', 
        'taxPercentage', 'tax', 'grandTotal', 'paymentStatus', 'amountPaid', 
        'workflowStage', 'paymentMethod', 'clientName', 'accountName', 
        'accountNumber', 'ifscCode', 'upiId', 'notes', 'thankYouMessage'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
