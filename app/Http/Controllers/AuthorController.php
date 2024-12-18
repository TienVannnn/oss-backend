<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Http\Request\Author\AuthorRequest;
use App\Http\Request\Author\EditAuthorRequest;
use App\Models\Author;
use App\Models\Image;
use App\Support\File\ImageSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    function createAuthor(AuthorRequest $request)
    {
        $data = $request->only(['full_name']);
        if(!empty($request->pen_name)){
            $data['pen_name'] = $request->pen_name;
        }
        if(!empty($request->birth_date)){
            $data['birth_date'] = $request->birth_date;
        }
        if(!empty($request->profile_picture_id)){
            $data['profile_picture_id'] = $request->profile_picture_id;
        }

        $author = Author::create($data);
        if(!$author){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'body' => [
                    'message' => 'Cannot create author'
                ]
            ], JsonResponse::HTTP_OK);
        }
        return response()->json([
            'status' => JsonResponse::HTTP_CREATED,
            'body' => [
                'data' => $author
            ]
        ], JsonResponse::HTTP_OK);
    }

    function listAuthors()
    {
        $authors = Author::with('profilePicture')->get();
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $authors
            ]
        ], JsonResponse::HTTP_OK);
    }

    function updateAuthor(EditAuthorRequest $request)
    {
        $data = array_filter(
            $request->all(['full_name', 'birth_date', 'profile_picture_id', 'pen_name']),
            fn($value) => !empty($value));
        $author = Author::find($request->id);
        if(!empty($data['profile_picture_id']) && $data['profile_picture_id'] != $author->profile_picture_id && $author->profile_picture_id != null){
            ImageSupport::delete($author->profile_picture_id);
        }
        $result = $author->update($data);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $author
            ]
        ], JsonResponse::HTTP_OK);

    }

    function deleteAuthor($id){
        $author = Author::find($id);
        if(!$author){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'body' => [
                    'message' => 'Cannot find author'
                ]
            ], JsonResponse::HTTP_OK);
        }
        if($author->profile_picture_id != null){
            ImageSupport::delete($author->profile_picture_id);
        }
        $author->delete();
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'message' => 'Author deleted'
            ]
        ]);
    }

    function detailAuthor($id)
    {
        $author = Author::with('profilePicture')->find($id);
        if (!$author) return Helpers::response(JsonResponse::HTTP_NOT_FOUND, "Author not found");
        return Helpers::response(JsonResponse::HTTP_OK, data: $author);
    }
}
