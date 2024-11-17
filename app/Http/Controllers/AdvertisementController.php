<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Http\Request\Advertisement\AdsRequest;
use App\Models\Advertisement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvertisementController extends Controller
{
    public function createAds(AdsRequest $request){
        $data = $request->only(['link', 'picture_id']);
        $result = Advertisement::create($data);
        if ($result){
            return Helpers::response(JsonResponse::HTTP_OK, 'Created Advertisement Successfully', $result);
        }
        return Helpers::response(JsonResponse::HTTP_BAD_REQUEST, 'Created Advertisement Failed');
    }

    public function updateAds(AdsRequest $request){
        $data = $request->only(['link', 'picture_id']);
        $adsId = $request->id ?? null;
        $ads = Advertisement::find($adsId);
        $result = $ads->update($data);
        if ($result){
            return Helpers::response(JsonResponse::HTTP_OK, 'Updated Advertisement Successfully', $result);
        }
        return Helpers::response(JsonResponse::HTTP_BAD_REQUEST, 'Updated Advertisement Failed');
    }

    public function deleteAds($id){
        $result = Advertisement::destroy($id);
        if ($result){
            return Helpers::response(JsonResponse::HTTP_OK, 'Deleted Advertisement Successfully');
        }
        return Helpers::response(JsonResponse::HTTP_NOT_FOUND, 'Deleted Advertisement Failed');
    }

    public function listAds(Request $request){
        $limit = $request->limit ?? 10;
        $offset = $request->offset ?? 0;
        $ads = Advertisement::limit($limit)->offset($offset)->get();
        return Helpers::response(JsonResponse::HTTP_OK, data: $ads);
    }

    public function getAdsRandom()
    {
        $ads = Advertisement::inRandomOrder()->first();
        return Helpers::response(JsonResponse::HTTP_OK, data: $ads);
    }
}
