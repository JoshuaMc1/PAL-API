<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AniList;
use App\Models\Anime;
use App\Models\FavoriteCharacter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AniListController extends Controller
{
    public function showList(Request $request)
    {
        try {
            $user = $request->user();
            $list = User::with('anilists')->find($user->id);
            $responseList = [];

            $latestAniLists = $list->aniLists()
                ->with('anime')
                ->latest()
                ->get();

            foreach ($latestAniLists as $value) {
                $newAniList = [
                    "anime_id" => $value['anime_id'],
                    "slug" => $value['anime']['slug'],
                    "data" => json_decode($value['anime']['anime'], true)
                ];
                array_push($responseList, $newAniList);
            }

            $response = [
                'success' => true,
                'data' => $responseList
            ];

            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function showAnime(Request $request, $slug)
    {
        try {
            $user = $request->user();

            $anime = Anime::where("slug", $slug)->first();

            if (!$anime) {
                return response()->json([
                    "success" => false,
                    "message" => "Al parecer ese anime no existe."
                ]);
            }

            $aniList = AniList::where("user_id", $user->id)
                ->where("anime_id", $anime->id)
                ->with("anime")
                ->first();

            $characters = FavoriteCharacter::where("list_id", $aniList->id)
                ->first();

            $animeResponse = json_decode($aniList['anime']['anime'], true);
            $responseCharacters = json_decode($characters["characters"], true);

            unset($aniList['anime'], $aniList['updated_at'], $aniList['user_id'], $aniList['id'], $aniList['status']);

            $aniList['anime'] = $animeResponse;
            $aniList['characters'] = $responseCharacters;

            return response()->json([
                "success" => true,
                "data" =>  $aniList,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getAnime($slug)
    {
        try {
            $anime = Anime::where("slug", $slug)->first();

            if (!$anime) {
                return response()->json([
                    "success" => false,
                    "message" => "Al parecer ese anime no existe."
                ]);
            }

            $responseAnime = json_decode($anime['anime'], true);
            $responseAnime['slug'] = $anime['slug'];

            return response()->json([
                "success" => true,
                "data" =>  $responseAnime,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function showRecentlyAdded(Request $request)
    {
        try {
            $user = $request->user();
            $data = User::with('aniLists')->find($user->id);
            $responseAniList = [];

            $latestAniLists = $data->aniLists()
                ->with('anime')
                ->latest()
                ->take(10)
                ->get();

            foreach ($latestAniLists as $value) {
                $newAniList = [
                    "anime_id" => $value['anime_id'],
                    "slug" => $value['anime']['slug'],
                    "data" => json_decode($value['anime']['anime'], true)
                ];
                array_push($responseAniList, $newAniList);
            }

            return response()->json([
                'success' => true,
                'data' => $responseAniList
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function showRecommendations()
    {
        try {
            $aniLists = AniList::select(
                'anime_id',
                DB::raw('SUM(overall_rating + animation_rating + history_rating + characters_rating + music_rating)/5 as total_rating')
            )
                ->groupBy('anime_id')
                ->with('anime')
                ->orderBy('total_rating', 'desc')
                ->take(15)
                ->get();
            $responseRecommendations = [];

            foreach ($aniLists as $value) {
                $newAniList = [
                    "anime_id" => $value['anime_id'],
                    "total_rating" => $value['total_rating'],
                    "slug" => $value['anime']['slug'],
                    "data" => json_decode($value['anime']['anime'], true)
                ];
                array_push($responseRecommendations, $newAniList);
            }

            return response()->json([
                'success' => true,
                'data' => $responseRecommendations
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function showHighestRating(Request $request)
    {
        try {
            $user = $request->user();
            $userId = $user->id;

            $aniLists = AniList::select(
                'anime_id',
                DB::raw('SUM(overall_rating + animation_rating + history_rating + characters_rating + music_rating)/5 as total_rating')
            )
                ->where('user_id', $userId)
                ->with('anime')
                ->groupBy('anime_id')
                ->orderBy('total_rating', 'desc')
                ->take(5)
                ->get();

            $responseHighest = [];

            foreach ($aniLists as $value) {
                $newAniList = [
                    "anime_id" => $value['anime_id'],
                    "total_rating" => $value['total_rating'],
                    "slug" => $value['anime']['slug'],
                    "data" => json_decode($value['anime']['anime'], true),
                ];
                array_push($responseHighest, $newAniList);
            }

            return response()->json([
                'success' => true,
                'data' => $responseHighest
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function create(Request $request)
    {
        try {
            $data = $request->all();
            $user = $request->user();

            $characters = $data['characters'];
            $general = $data['general'];
            $anime = $data['anime'];

            $userAniList = AniList::where('user_id', '=', $user['id'])
                ->where('anime_id', '=', $anime['mal_id'])
                ->get();

            if (sizeof($userAniList) > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Al parecer ya tienes este anime agregado en tu lista...'
                ]);
            }

            if (!Anime::where('id', '=', $anime['mal_id'])->exists()) {
                $saveAnime = Anime::create([
                    'id' => $anime['mal_id'],
                    'slug' => str_replace(' ', '-', strtolower($anime['title'])),
                    'anime' => json_encode($anime),
                ]);
            }

            $aniList = AniList::create([
                'user_id' => $user['id'],
                'anime_id' => $anime['mal_id'],
                'overall_rating' => $general['overall_rating'],
                'animation_rating' => $general['animation_rating'],
                'history_rating' => $general['history_rating'],
                'characters_rating' => $general['characters_rating'],
                'music_rating' => $general['music_rating'],
                'the_good' => $general['the_good'],
                'the_bad' => $general['the_bad'],
                'currently' => $general['currently']
            ]);

            if ($aniList) {
                $characters = FavoriteCharacter::create([
                    'list_id' => $aniList['id'],
                    'characters' => json_encode($characters)
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'El anime se a añadido correctamente...',
                    'slug' => $saveAnime['slug']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Al parecer a ocurrido un error...'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $data = $request->all();
            $user = $request->user();

            $characters = $data['characters'];
            $general = $data['general'];
            $anime = $data['anime'];

            $aniList = AniList::where('user_id', $user->id)
                ->where("anime_id", $anime['mal_id'])
                ->with("anime")
                ->first();

            if (!$aniList) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para actualizar este registro...'
                ]);
            }

            $aniList->overall_rating = $general['overall_rating'];
            $aniList->animation_rating = $general['animation_rating'];
            $aniList->history_rating = $general['history_rating'];
            $aniList->characters_rating = $general['characters_rating'];
            $aniList->music_rating = $general['music_rating'];
            $aniList->the_good = $general['the_good'];
            $aniList->the_bad = $general['the_bad'];
            $aniList->currently = $general['currently'];
            $aniList->save();

            $favoriteCharacters = FavoriteCharacter::where('list_id', $aniList->id)->first();

            if (!$favoriteCharacters) {
                return response()->json([
                    'success' => false,
                    'message' => 'Al parecer a ocurrido un error al obtener los personajes favoritos...'
                ]);
            }

            $favoriteCharacters->characters = json_encode($characters);
            $favoriteCharacters->save();

            return response()->json([
                'success' => true,
                'message' => 'El anime se ha actualizado correctamente...',
                'slug' => $aniList['anime']['slug'],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
