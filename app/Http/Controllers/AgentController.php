<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Team;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function index() {
        $rol = auth()->user()->roles;
        $rol = json_decode($rol[0]);
        if($rol->id == 1) {
            $agents = Agent::paginate(25);
        } else {
            $agents = Agent::where('active','=',1)->paginate(25);
        }
        $team = Team::all();
        return view('admin.agent.index', ['agents' => $agents, 'teams' => $team]);
    }

    public function create(Request $request) {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|unique:agents',
        ]);

        $agent = new Agent;
        $agent->name = $request->get('name');
        $agent->email = $request->get('email');
        $team = explode(' ',$request->get('team'));
        $agent->team = $team[0];
        $agent->active = 1;

        $agent->save();

        return redirect()->route('agents')->with('success','Agent created successfuly');
    }

    public function update(Request $request, $id) {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required',
        ]);

        $agent = Agent::find($id);
        $agent->name = $request->get('name');
        $agent->email = $request->get('email');
        $team = explode(' ',$request->get('team'));
        $agent->team = $team[0];
        $agent->active = ($request->get('active')=='on') ? 1 : 0;

        $agent->save();

        return redirect(url()->previous())->with('success','Agent updated successfuly');
    }

}
