<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Esdeveniment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Display a listing of the reviews for a specific event.
     */
    public function index($esdeveniment_id)
    {
        $esdeveniment = Esdeveniment::findOrFail($esdeveniment_id);

        $reviews = Review::with('user:id,name')
            ->where('esdeveniment_id', $esdeveniment_id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'average_rating' => $esdeveniment->average_rating,
            'total_reviews' => $reviews->count()
        ]);
    }

    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'esdeveniment_id' => 'required|exists:esdeveniments,id',
            'nom_usuari' => 'required|string|max:50',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = Auth::id() ?: 1; // Assignem ID 1 (Admin) o null com a fallback si no hi ha usuari
        $esdevenimentId = $request->esdeveniment_id;

        // Comprovar si l'usuari ja ha deixat una ressenya (només si està loguejat realment)
        if (Auth::check()) {
            $existingReview = Review::where('user_id', Auth::id())
                ->where('esdeveniment_id', $esdevenimentId)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ja has deixat una ressenya per aquest esdeveniment.'
                ], 409);
            }
        }

        $review = Review::create([
            'user_id' => Auth::id(),
            'nom_usuari' => $request->nom_usuari,
            'esdeveniment_id' => $esdevenimentId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ressenya creada correctament.',
            'data' => $review->load('user:id,name')
        ], 201);
    }
}