<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\MenuService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * Display the dark-themed main dashboard.
     */
    public function index(Request $request)
    {
        $menus = $this->menuService->getMenuTree();

        // Sample dashboard metrics for the e-shop
        $metrics = [
            'today_sales' => 1248.50,
            'today_orders' => 38,
            'pos_shifts_active' => 2,
            'low_stock_items' => 9,
            'pending_quotations' => 4,
            'total_customers' => 482,
        ];

        $recentOrders = [
            [
                'id' => 'ORD-8921',
                'customer' => 'Sokha Mean',
                'type' => 'POS Walk-in',
                'amount' => '$45.00',
                'payment' => 'ABA QR',
                'status' => 'Completed',
                'time' => '10 mins ago',
            ],
            [
                'id' => 'ORD-8920',
                'customer' => 'Chen Wei',
                'type' => 'Online Store',
                'amount' => '$120.00',
                'payment' => 'Credit Card',
                'status' => 'Processing',
                'time' => '25 mins ago',
            ],
            [
                'id' => 'ORD-8919',
                'customer' => 'Kimleng Seng',
                'type' => 'POS Walk-in',
                'amount' => '$18.50',
                'payment' => 'Cash',
                'status' => 'Completed',
                'time' => '40 mins ago',
            ],
            [
                'id' => 'ORD-8918',
                'customer' => 'David Brown',
                'type' => 'Indoor Order',
                'amount' => '$67.20',
                'payment' => 'Bank Transfer',
                'status' => 'Completed',
                'time' => '1 hour ago',
            ],
            [
                'id' => 'ORD-8917',
                'customer' => 'Lin Zhang',
                'type' => 'Quotation Convert',
                'amount' => '$350.00',
                'payment' => 'Pending Deposit',
                'status' => 'Pending',
                'time' => '2 hours ago',
            ],
        ];

        return view('backend.dashboard', compact('menus', 'metrics', 'recentOrders'));
    }
}
