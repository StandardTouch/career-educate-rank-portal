<?php

namespace App\Http\Controllers;

class All_india_quota_bds2023AnalysisController extends GenericPredictorController
{
    protected string $mainTable = 'all_india_quota_bds_2023';
    protected string $roundTable = 'all_india_quota_bds_2023_rounds';
    protected string $viewName = 'all_india_quota_bds_2023_analysis';
    protected string $stateLabel = 'All India Quota Bds';
    protected string $pageTitle = 'All India Quota Bds 2023 Analysis';
}
?>