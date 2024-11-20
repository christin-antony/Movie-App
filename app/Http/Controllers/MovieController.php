<?php

namespace App\Http\Controllers;

use App\Models\SearchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MovieController extends Controller
{
    public function index()
    {
        $searchHistories = SearchHistory::latest()->take(5)->get();
        return view('movies.index', compact('searchHistories'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $fromHistory = $request->input('fromHistory', false);

        if (empty($query)) {
            return response()->json(['error' => 'Please enter a movie title to search.'], 400);
        }

        try {
            // Make API call to your movie API
            $response = Http::post('https://dev.maquae.com/movie-api/api.php', [
                'query' => $query
            ]);

            $data = $response->json();

            // Only save to history if it's a new search (not from clicking history)
            if (!$fromHistory) {
                // Check if this query already exists
                $exists = SearchHistory::where('query', $query)->exists();
                
                if (!$exists) {
                    SearchHistory::create(['query' => $query]);
                } else {
                    // Update timestamp of existing query to move it to top
                    SearchHistory::where('query', $query)->update(['updated_at' => now()]);
                }
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching the movie data.'], 500);
        }
    }

    public function loadMore()
    {
        $searchHistories = SearchHistory::latest()->get();
        return response()->json($searchHistories);
    }

    public function deleteHistory($id)
    {
        try {
            SearchHistory::findOrFail($id)->delete();
            return response()->json(['message' => 'Search history deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete search history'], 500);
        }
    }
}