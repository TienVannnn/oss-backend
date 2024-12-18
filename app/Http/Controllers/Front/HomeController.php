<?php

namespace App\Http\Controllers\Front;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Jobs\LogSearchQuery;
use App\Models\Author;
use App\Models\Category;
use App\Models\Comics;
use App\Models\NovelCategory;
use App\Models\Story;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function completed_stories(Request $request)
    {
        $limit = $request->input('limit', 12);
        $offset = $request->input('offset', 0);
        $stories = Story::with('categories')
            ->where('status', 2)
            ->offset($offset)
            ->paginate($limit);
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $stories
            ]
        ], JsonResponse::HTTP_OK);
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        if (!$query) {
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'body' => [
                    'message' => 'Invalid parameters'
                ]
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        $result = [];
        $storyResults = Story::where('name', 'LIKE', "%{$query}%")
            ->where('status', '!=', 0)
            ->get()
            ->map(function ($story) {
                $story->type = 'story';
                return $story;
            });
        $comicResults = Comics::where('name', 'LIKE', "%{$query}%")
            ->where('status', 1)
            ->get()
            ->map(function ($comic) {
                $comic->type = 'comic';
                return $comic;
            });

        $combinedResults = $storyResults->merge($comicResults);
        $authorResult = Author::where('full_name', 'LIKE', "%{$query}%")
            ->orWhere('pen_name', 'LIKE', "%{$query}%")
            ->get();

        $result['authors'] = $authorResult;
        $result['novels'] = $combinedResults;

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }


    public function latestStories(Request $request)
    {
        $limit = $request->limit ?? 20;

        $stories = Story::where('status', '!=', 0)
            ->whereHas('chapters')
            ->with(['categories', 'chapters' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->get()
            ->map(function ($story) {
                $story->chapter = $story->chapters->first();
                unset($story->chapters);
                return $story;
            })
            ->sortByDesc(function ($story) {
                return $story->chapter ? $story->chapter->created_at : 0;
            })
            ->take($limit);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $stories->values()
            ]
        ]);
    }


    public function hotNovels($slugCategory = null)
    {
        $limit = 16;
        if (!$slugCategory) {
            $data = NovelCategory::with('novel')->get()
                ->unique('novel_id')
                ->filter(function ($novel) {
                    return $novel->novel->status != 0;
                })
                ->sortByDesc(function ($novel) {
                    return $novel->novel->views ?? 0;
                })->take($limit)->shuffle()->values()->toArray();
            return Helpers::response(JsonResponse::HTTP_OK, data: $data);
        }

        $category = Category::where('slug', $slugCategory)->where('status', 1)->first();
        if (!$category) {
            return response()->json([
                'status' => JsonResponse::HTTP_NOT_FOUND,
                'body' => [
                    'message' => 'Category not found'
                ]
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        $data = NovelCategory::where('category_id', $category->id)
            ->with('novel')->get()
            ->unique('novel_id')
            ->filter(function ($novel) {
                return $novel->novel->status != 0;
            })
            ->sortByDesc(function ($novel) {
                return $novel->novel->views ?? 0;
            })->take($limit)->shuffle()->values()->toArray();
        return Helpers::response(JsonResponse::HTTP_OK, data: $data);
    }
}
