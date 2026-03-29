<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\EnquiryModel;
use App\Models\InvoiceModel;
use App\Models\QuotationModel;
use App\Models\ContactModel;
use App\Models\TestimonialModel;
use App\Models\GalleryModel;
use App\Models\SliderModel;
use App\Models\LoveStoryModel;

class Dashboard extends ResourceController
{
    protected $format = 'json';

    /**
     * GET /api/dashboard/stats
     */
    public function stats()
    {
        try {
            $enquiryModel = new EnquiryModel();
            $invoiceModel = new InvoiceModel();
            $quoteModel = new QuotationModel();
            $contactModel = new ContactModel();
            $testiModel = new TestimonialModel();
            $galleryModel = new GalleryModel();
            $sliderModel = new SliderModel();
            $storyModel = new LoveStoryModel();

            $today = date('Y-m-d');
            $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
            $startOfMonth = date('Y-m-01');

            // KPI Calculations
            $kpi = [
                'newEnquiriesWeek' => $enquiryModel->where('created_at >=', $sevenDaysAgo)->countAllResults(),
                'newEnquiriesToday' => $enquiryModel->where('DATE(created_at)', $today)->countAllResults(),
                'newOrdersCount' => $invoiceModel->where('invoiceDate >=', $startOfMonth)->countAllResults(),
                'pendingQuotations' => $quoteModel->whereIn('status', ['Draft', 'Sent'])->countAllResults(),
                'unpaidInvoicesCount' => $invoiceModel->whereIn('paymentStatus', ['Unpaid', 'Partially Paid', 'Partial', 'Overdue'])->countAllResults(),
                'unpaidInvoicesAmount' => $invoiceModel->whereIn('paymentStatus', ['Unpaid', 'Partially Paid', 'Partial', 'Overdue'])->selectSum('grandTotal')->first()['grandTotal'] ?? 0,
                'upcomingShootsCount' => $invoiceModel->where('eventDate >=', $today)->where('eventDate <=', date('Y-m-d', strtotime('+7 days')))->countAllResults(),
                'unreadMessages' => $contactModel->where('status', 'New')->countAllResults(),
                'pendingTestimonials' => $testiModel->where('status', 'Inactive')->countAllResults(),
                'galleryQueue' => $galleryModel->where('status', 'Inactive')->countAllResults(),
            ];

            // Action Required
            $actionRequired = [
                'enquiriesNoReply' => array_map(function($e) { $e['_id'] = $e['id']; return $e; }, $enquiryModel->where('status', 'New')->limit(4)->find()),
                'overdueInvoices' => array_map(function($i) { $i['_id'] = $i['id']; return $i; }, $invoiceModel->where('paymentStatus', 'Overdue')->limit(4)->find()),
                'pendingTestimonialsList' => array_map(function($t) { $t['_id'] = $t['id']; return $t; }, $testiModel->where('status', 'Inactive')->limit(4)->find()),
            ];

            // Pipeline
            $pipeline = [
                ['_id' => 'Planning', 'count' => $invoiceModel->where('workflowStage', 'Planning')->countAllResults()],
                ['_id' => 'Production', 'count' => $invoiceModel->where('workflowStage', 'Production')->countAllResults()],
                ['_id' => 'Editing', 'count' => $invoiceModel->where('workflowStage', 'Editing')->countAllResults()],
                ['_id' => 'Delivered', 'count' => $invoiceModel->where('workflowStage', 'Delivered')->countAllResults()],
            ];

            // Schedule
            $schedule = array_map(function($evt) { $evt['_id'] = $evt['id']; return $evt; }, $invoiceModel->select('id, clientName as name, eventType as event_name, eventDate as event_date, workflowStage as order_status')
                                    ->where('eventDate >=', date('Y-m-d', strtotime('-30 days')))
                                    ->where('eventDate <=', date('Y-m-d', strtotime('+60 days')))
                                    ->orderBy('eventDate', 'ASC')
                                    ->findAll());

            // Revenue
            $revenue = [
                'totalOutstanding' => $invoiceModel->whereIn('paymentStatus', ['Unpaid', 'Partially Paid', 'Partial', 'Overdue'])->selectSum('grandTotal')->first()['grandTotal'] ?? 0,
                'thisMonthCollected' => $invoiceModel->where('invoiceDate >=', $startOfMonth)->selectSum('amountPaid')->first()['amountPaid'] ?? 0,
                'thisMonthBilled' => $invoiceModel->where('invoiceDate >=', $startOfMonth)->selectSum('grandTotal')->first()['grandTotal'] ?? 0,
            ];

            // Content Health
            $contentHealth = [
                'sliderActive' => $sliderModel->countAllResults(),
                'storiesPublished' => $storyModel->countAllResults(),
                'testimonialsPublished' => $testiModel->countAllResults(),
            ];

            // Charts
            $charts = [
                'monthlyRevenue' => [],
                'monthlyOrders' => [],
                'invoiceStatus' => [
                    ['_id' => 'Paid', 'value' => $invoiceModel->where('paymentStatus', 'Paid')->countAllResults()],
                    ['_id' => 'Pending', 'value' => $invoiceModel->where('paymentStatus', 'Unpaid')->countAllResults()],
                    ['_id' => 'Overdue', 'value' => $invoiceModel->where('paymentStatus', 'Overdue')->countAllResults()],
                ]
            ];

            $activityFeed = [
                ['text' => 'System dashboard updated', 'date' => date('Y-m-d H:i:s'), 'type' => 'System'],
            ];

            $ordersByType = $invoiceModel->select('eventType as _id, count(*) as count')->groupBy('eventType')->findAll();

            return $this->respond([
                'kpi' => $kpi,
                'actionRequired' => $actionRequired,
                'pipeline' => $pipeline,
                'schedule' => $schedule,
                'revenue' => $revenue,
                'activityFeed' => $activityFeed,
                'charts' => $charts,
                'ordersByType' => $ordersByType,
                'contentHealth' => $contentHealth
            ]);

        } catch (\Exception $e) {
            log_message('error', '[Dashboard::stats] ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
