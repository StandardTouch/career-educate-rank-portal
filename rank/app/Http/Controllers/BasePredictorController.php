<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

abstract class BasePredictorController extends Controller
{
    /**
     * The state slug (e.g. "karnataka", "all_india")
     */
    protected string $state;

    /**
     * The year (e.g. 2025)
     */
    protected int $year;

    /**
     * Optional descriptor (e.g. "management", "govt_quota").
     * Empty string means the default predictor for the state/year.
     */
    protected string $descriptor = '';

    /** Resolve the main model class name based on the state, year and descriptor. */
    protected function getModelClass(): string
    {
        $class = 'App\\Models\\' . ucfirst($this->state) . $this->year;
        if (!empty($this->descriptor)) {
            $class .= ucfirst($this->descriptor);
        }
        return $class;
    }

    /** Resolve the rounds model class name. */
    protected function getRoundsModelClass(): string
    {
        return $this->getModelClass() . 'Round';
    }

    /** Main entry point – works for normal page loads and for AJAX DataTables calls. */
    public function index(Request $request)
    {
        $view = $this->state . '_' . $this->year;
        if (!empty($this->descriptor)) {
            $view .= '_' . $this->descriptor;
        }

        if ($request->ajax()) {
            $modelClass = $this->getModelClass();
            $query = $modelClass::query();

            $filterable = ['college_name','category','local_area','quota','admission','rank','fees','tuition_fee','total_fee','seat_type'];
            foreach ($filterable as $col) {
                if ($request->filled($col)) {
                    $query->where($col, 'like', "%{$request->input($col)}%");
                }
            }

            return DataTables::of($query)->make(true);
        }

        $modelClass = $this->getModelClass();
        $distinct = function (string $column) use ($modelClass) {
            return $modelClass::select($column)->distinct()->pluck($column)->toArray();
        };

        return View::make($view, [
            'state' => $this->state,
            'year' => $this->year,
            'descriptor' => $this->descriptor,
            'colleges' => $distinct('college_name'),
            'categories' => $distinct('category'),
            'localAreas' => $distinct('local_area'),
            'quotas' => $distinct('quota'),
            'rounds' => $this->getRoundsModelClass()::select('round_id')->distinct()->pluck('round_id')->toArray(),
        ]);
    }
}
?>
