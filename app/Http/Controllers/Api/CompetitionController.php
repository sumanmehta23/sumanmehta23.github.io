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

    public function getTraderData($account)
    {
        $data = $this->competitionService->getTraderData($account);
        return response()->json($data);
    }

    public function getCurrentCompetition()
    {
        $stats = $this->competitionService->getCurrentStats();
        return response()->json($stats);
    }
}
