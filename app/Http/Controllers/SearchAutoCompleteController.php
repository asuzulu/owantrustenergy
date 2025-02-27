<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Search;

class SearchAutoCompleteController extends Controller
{
    /**
     * Search for customers matching the query.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchCustomers(Request $request)
    {
        $query = $request->query('query');
        $customers = Search::searchCustomers($query);
        return response()->json($customers);
    }

    /**
     * Search for drivers matching the query.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchDrivers(Request $request)
    {
        $query = $request->query('query');
        $drivers = Search::searchDrivers($query);
        return response()->json($drivers);
    }
}
