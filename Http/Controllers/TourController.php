<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TourController extends Controller
{
    public function index(Request $request)
    {
        // Start query for Morocco tours only
        $query = Tour::with(['destination'])
            ->whereHas('destination', function($q) {
                $q->where('country', 'Morocco');
            })
            ->where('status', 'published');
        
        // Apply filters
        if ($request->has('destination')) {
            $destinationId = Destination::where('city', 'like', '%' . $request->destination . '%')
                ->where('country', 'Morocco')
                ->pluck('id');
            $query->whereIn('destination_id', $destinationId);
        }
        
        if ($request->has('tour_type')) {
            $query->where('tour_type', $request->tour_type);
        }
        
        if ($request->has('duration')) {
            $duration = explode('-', $request->duration);
            if (count($duration) == 2) {
                $query->whereBetween('duration_days', [$duration[0], $duration[1]]);
            }
        }
        
        if ($request->has('price')) {
            $price = explode('-', $request->price);
            if (count($price) == 2) {
                $query->whereBetween('price_per_person', [$price[0], $price[1]]);
            } elseif ($request->price == '2000+') {
                $query->where('price_per_person', '>=', 2000);
            }
        }
        
        // Get featured tours
        $featuredTours = Tour::with(['destination'])
            ->whereHas('destination', function($q) {
                $q->where('country', 'Morocco');
            })
            ->where('is_featured', true)
            ->where('status', 'published')
            ->take(8)
            ->get();
        
        // Get all tours with pagination
        $tours = $query->paginate(6);
        
        // Get Moroccan cities for filter
        $moroccanCities = Destination::where('country', 'Morocco')
            ->select('city')
            ->distinct()
            ->pluck('city');
        
        // Moroccan tour categories
        $categories = [
            (object)[
                'name' => 'Desert',
                'icon' => 'bi bi-sun',
                'type' => 'desert'
            ],
            (object)[
                'name' => 'Cultural',
                'icon' => 'bi bi-palette',
                'type' => 'cultural'
            ],
            (object)[
                'name' => 'Atlas',
                'icon' => 'bi bi-geo',
                'type' => 'adventure'
            ],
            (object)[
                'name' => 'Imperial',
                'icon' => 'bi bi-building',
                'type' => 'cultural'
            ],
            (object)[
                'name' => 'Beach',
                'icon' => 'bi bi-umbrella',
                'type' => 'beach'
            ],
            (object)[
                'name' => 'Luxury',
                'icon' => 'bi bi-gem',
                'type' => 'luxury'
            ]
        ];
        
        // Special offers for Morocco
        $specialOffers = Tour::with(['destination'])
            ->whereHas('destination', function($q) {
                $q->where('country', 'Morocco');
            })
            ->whereNotNull('discount_price')
            ->where('status', 'published')
            ->take(2)
            ->get();
        
        return view('pages.tours.index', compact(
            'featuredTours', 
            'tours', 
            'categories', 
            'specialOffers', 
            'moroccanCities'
        ));
    }
    
 public function show($slug)
    {
        $tour = Tour::with(['destination', 'reviews.user', 'bookings'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();
        
        // Calculate average rating
        $averageRating = $tour->reviews->where('is_approved', true)->avg('rating');
        $reviewsCount = $tour->reviews->where('is_approved', true)->count();
        
        // Get related tours (same destination or similar duration)
        $relatedTours = Tour::with(['destination'])
            ->where('id', '!=', $tour->id)
            ->where('status', 'published')
            ->whereHas('destination', function($q) {
                $q->where('country', 'Morocco');
            })
            ->where(function($query) use ($tour) {
                // Find tours in same destination or similar duration
                $query->where('destination_id', $tour->destination_id)
                    ->orWhereBetween('duration_days', [$tour->duration_days - 2, $tour->duration_days + 2]);
            })
            ->orderBy('is_featured', 'desc')
            ->take(3)
            ->get();
        
        // Parse itinerary from description or use a default
        $itinerary = $this->extractItinerary($tour->description);
        
        return view('pages.tours.show', compact('tour', 'relatedTours', 'averageRating', 'reviewsCount', 'itinerary'));
    }
    
    private function extractItinerary($description)
    {
        // This is a simple extraction method. You might want to store itinerary separately
        // For now, we'll use the description and add day markers
        $lines = explode("\n", $description);
        $itinerary = '';
        $dayCount = 1;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                // Check if line looks like a day description
                if (preg_match('/day\s+\d+/i', $line) || preg_match('/^\d+\./', $line)) {
                    $itinerary .= "<div class='itinerary-item'><div class='day-number'>Day $dayCount</div>";
                    $itinerary .= "<div class='day-content'><h4>" . ucfirst($line) . "</h4>";
                    $dayCount++;
                } else {
                    $itinerary .= "<p>$line</p>";
                }
            }
        }
        
        return $itinerary;
    }

}
