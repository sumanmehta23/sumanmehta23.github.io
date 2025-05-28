<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CompetitionService;
use Illuminate\Http\Request;

class CompetitionController extends Controller
{
    protected $competitionService;

    public function __construct(CompetitionService $competitionService)
    {
        $this->competitionService = $competitionService;
    }

    public function getTraderData($account, Request $request)
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 10);
        $data = $this->competitionService->getTraderData($account, $page, $perPage);
        return response()->json($data);
    }

    public function getCurrentCompetition()
    {
        $stats = $this->competitionService->getCurrentStats();
        return response()->json($stats);
    }
}
