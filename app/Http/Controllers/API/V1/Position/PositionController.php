<?php

namespace App\Http\Controllers\API\V1\Position;

use App\Http\Controllers\Controller;
use App\Models\Position\Position;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $positions = $this->getPositions();
        if ($positions->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Positions not found'], 404);
        }
        return response()->json(['success' => true, 'positions' => $positions]);
    }

    /**
     * @return Collection
     */
    private function getPositions(): Collection
    {
        return Position::query()->select(['id', 'name'])->get();
    }
}
