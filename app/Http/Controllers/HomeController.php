<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\Answer;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $dateS = Carbon::now()->startOfMonth();
        $dateE = Carbon::now()->endOfMonth(); 

        $answers = Answer::whereIn('status',[2,4])->whereBetween('expiration',[$dateS,$dateE])->get();
        $c_answers = count($answers);

        $dateS = Carbon::now()->startOfMonth()->subMonth(1);
        $dateE = Carbon::now()->endOfMonth()->subMonth(1); 

        $answers = Answer::whereIn('status',[2,4])->whereBetween('expiration',[$dateS,$dateE])->get();
        $old_answers = count($answers);

        $percentatge = (($c_answers - $old_answers)/$old_answers)*100;

        return view('home', ['complete_answers' => $c_answers, 'percentatge' => $percentatge]);
    }
}
