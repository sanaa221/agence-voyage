<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Get counts only (no complex queries initially)
            $stats = [
                'totalTours' => Tour::count(),
                'totalBookings' => Booking::count(),
                'totalUsers' => User::where('is_admin', false)->count(),
                'totalRevenue' => Payment::where('status', 'completed')->sum('amount'),
                'pendingBookings' => Booking::where('status', 'pending')->count(),
                'pendingReviews' => Review::where('is_approved', false)->count(),
            ];

            // Get limited data for recent bookings
            $recentBookings = Booking::with(['user:id,name,email', 'tour:id,title'])
                ->latest()
                ->take(5)
                ->get(['id', 'booking_reference', 'user_id', 'tour_id', 'start_date', 'number_of_guests', 'total_price', 'status']);

            // Get limited data for popular tours
            $popularTours = Tour::with(['destination:id,city'])
                ->withCount('bookings')
                ->orderBy('bookings_count', 'desc')
                ->take(5)
                ->get(['id', 'title', 'destination_id', 'price_per_person']);

            return view('admin.index', compact(
                'stats', 
                'recentBookings', 
                'popularTours'
            ));

        } catch (\Exception $e) {
            // Log error and show simple dashboard
            
            // Return minimal data
            $stats = [
                'totalTours' => 0,
                'totalBookings' => 0,
                'totalUsers' => 0,
                'totalRevenue' => 0,
                'pendingBookings' => 0,
                'pendingReviews' => 0,
            ];
            
            return view('admin.index', compact('stats'));
        }
    }
}
