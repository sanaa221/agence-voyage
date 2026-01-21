<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;

class DestinationController
{
      public function index()
    {
        $destinations = Destination::where('country', 'Morocco')
        ->orderBy('name')
            ->paginate(9);
            
        return view('pages.destinations.index', compact('destinations'));
    }

 
    public function show($id)
    {
        $destination = Destination::with('tours')->findOrFail($id);
        $relatedTours = $destination->tours()
            ->where('status', 'published')
            ->orderBy('is_featured', 'desc')
            ->take(6)
            ->get();
            
        return view('pages.destinations.show', compact('destination', 'relatedTours'));
    }
}
