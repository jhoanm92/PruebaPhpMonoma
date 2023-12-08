<?php

namespace App\Http\Controllers;

use App\Candidate;
use App\Http\Requests\Api\Candidate\StoreRequest;
use App\Http\Resources\CandidateRersource;
use Exception;
use Illuminate\Http\JsonResponse;

class CandidateController extends Controller
{
    /**
     * It creates a new candidate resource
     *
     * @param StoreRequest $request The request object
     *
     * @return JsonResponse candidate resource
     */
    public function store(StoreRequest $request): JsonResponse
    {
        if($request->user()->cannot('create', Candidate::class)){
            return response()->error('You are not authorized to create a lead', 403);
        }
        try {
            $request->validated();
            $user=auth()->user();

            $candidate = Candidate::create([
                'name' => $request->name,
                'source' => $request->source,
                'user_id' => $request->user_id,
                'created_by' => $user->id,
            ]);

            return response()->success(
                new CandidateRersource($candidate),
                200
            );
        } catch (Exception $ex) {
            return response()->error($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * It returns a list of candidate resources
     *
     * @return JsonResponse list of candidate resources
     */
    public function index(): JsonResponse
    {
        try {
            $this->authorize('viewAny', Candidate::class);

            if (auth()->user()->role == 'agent') {
                $candidates = Candidate::where('user_id', auth()->user()->id)->get();
            } else {
                $candidates = Candidate::all();
            }

            return response()->success(
                CandidateRersource::collection($candidates),
                200
            );
        } catch (Exception $ex) {
            return response()->error($ex->getMessage(), 500);
        }
    }

    
    /**
     * It returns a candidate resource
     *
     * @param string $id The candidate id.
     *
     * @return JsonResponse candidate resource
     */
    public function show(string $id): JsonResponse
    {
        try {
            $candidate = Candidate::find($id);

            if (!$candidate) {
                return response()->error('Candidate not found', 404);
            }

            if(auth()->user()->cannot('view', $candidate)){
                return response()->error('You are not authorized to view this lead', 403);
            }

            return response()->success(
                new CandidateRersource($candidate),
                200
            );
        } catch (Exception $ex) {
            return response()->error($ex->getMessage(), 500);
        }
    }
}
