<?php

namespace App\Http\Controllers;

use App\Models\Umkm;
use App\Models\UmkmOrder;
use App\Models\UmkmProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UmkmSellerController extends Controller
{
    /**
     * Seller Centre Dashboard
     */
    public function dashboard(Request $request, $token)
    {
        $umkm = Umkm::where('manage_token', $token)->firstOrFail();
        
        // Verify ownership via session or token (simplification for now)
        // In production, we use the Portal session
        
        $metrics = [
            'total_sales' => $umkm->orders()->where('status', 'completed')->sum('total_price'),
            'new_orders' => $umkm->orders()->where('status', 'pending')->count(),
            'to_ship' => $umkm->orders()->where('status', 'packing')->count(),
            'total_products' => $umkm->products()->count(),
            'low_stock' => $umkm->products()->where('stock', '<', 5)->count(),
            'performance_score' => $this->calculatePerformance($umkm),
        ];

        $recent_orders = $umkm->orders()->latest()->take(5)->get();
        
        // Sales performance for chart (last 7 days)
        $sales_chart = $umkm->orders()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('public.umkm_rakyat.seller.dashboard', compact('umkm', 'metrics', 'recent_orders', 'sales_chart'));
    }

    /**
     * Order Management
     */
    public function orders(Request $request, $token)
    {
        $umkm = Umkm::where('manage_token', $token)->firstOrFail();
        $status = $request->get('status', 'all');
        
        $query = $umkm->orders();
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        $orders = $query->latest()->paginate(10);
        
        return view('public.umkm_rakyat.seller.orders', compact('umkm', 'orders', 'status'));
    }

    /**
     * Update Order Status
     */
    public function updateOrderStatus(Request $request, $token, $orderId)
    {
        $umkm = Umkm::where('manage_token', $token)->firstOrFail();
        $order = $umkm->orders()->findOrFail($orderId);
        
        $request->validate([
            'status' => 'required|in:pending,packing,sent,completed,cancelled',
            'tracking_number' => 'nullable|string'
        ]);

        $order->update([
            'status' => $request->status,
            'tracking_number' => $request->tracking_number ?? $order->tracking_number
        ]);

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    /**
     * Product Inventory Management
     */
    public function inventory(Request $request, $token)
    {
        $umkm = Umkm::where('manage_token', $token)->firstOrFail();
        $products = $umkm->products()->latest()->get();
        
        return view('public.umkm_rakyat.seller.inventory', compact('umkm', 'products'));
    }

    /**
     * Update Stock Quickly
     */
    public function updateStock(Request $request, $token, $productId)
    {
        $umkm = Umkm::where('manage_token', $token)->firstOrFail();
        $product = $umkm->products()->findOrFail($productId);
        
        $request->validate(['stock' => 'required|integer|min:0']);
        
        $product->update(['stock' => $request->stock]);
        
        return response()->json(['success' => true, 'new_stock' => $product->stock]);
    }

    private function calculatePerformance($umkm)
    {
        $total = $umkm->orders()->count();
        if ($total == 0) return 100;
        
        $completed = $umkm->orders()->where('status', 'completed')->count();
        $cancelled = $umkm->orders()->where('status', 'cancelled')->count();
        
        // Simple formula: (Completed / (Total - Cancelled)) * 100
        $effectiveTotal = $total - $cancelled;
        if ($effectiveTotal <= 0) return 100;
        
        return round(($completed / $effectiveTotal) * 100);
    }
}
