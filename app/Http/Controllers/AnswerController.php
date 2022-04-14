<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Answer;
use App\Models\Client;
use App\Models\Store;
use App\Models\Task;
use App\Models\Incidence;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mail;
use App\Mail\NotifyMail;

class AnswerController extends Controller
{
    public function index() {
        $answers = Answer::where('status','!=',2)->sortable()->paginate(10);
        $stores = Store::all();
        $clients = Client::all();

        $id = auth()->user()->id;

        return view('admin.answer.index',['answers' => $answers, 'stores' => $stores, 'clients' => $clients, 'id' => $id]);
    }

    public function view($id) {
        $answer = Answer::find($id);

        //Add user and modify status
        $answer->status = 1;
        $answer->user = auth()->user()->id;
        $answer->save();

        $store = Store::where('code','=',$answer->store)->where('client','=',$answer->client)->first();
        $agents = Agent::all();
        $tasks = json_decode($answer->tasks);
        foreach($tasks as $task) {
            $owners[] = $task->owner;
        }

        $owners = Agent::find($owners);

        return view('admin.answer.view', ['answer' => $answer, 'store' => $store, 'agents' => $agents, 'owners' => $owners]);
    }

    public function response(Request $request, $id) {
        $body = [];
        $body['valoration'][0] = $request->get('valoration1');
        $body['valoration'][1] = $request->get('valoration2');
        $body['valoration'][2] = $request->get('valoration3');
        $body['valoration'][3] = $request->get('valoration4');
        $body['comment'][0] = $request->get('comment1');
        $body['comment'][1] = $request->get('comment2');
        $body['comment'][2] = $request->get('comment3');
        $body['comment'][3] = $request->get('comment4');

        $answer = Answer::find($id);

        $answer->status = 2;
        $answer->answer = json_encode($body,true);

        $answer->save();
        $body = null;

        if($request['responsable'] != null) {
            foreach($request->get('responsable') as $index => $responsable ) {
                $body[0]['message'] = $request['incidence'][$index];
                $body[0]['owner'] = auth()->user()->name;
                $body[0]['type'] = 'user';
    
                $task = explode('-',$request['responsable'][$index]);
    
                $ot = Task::where('code','=',$task[1])->first();
    
                $incidence = new Incidence();
    
                $incidence->responsable = auth()->user()->id; 
                $incidence->owner = $task[0];
                $incidence->impact = $request['impact'][$index];
                $incidence->status = 0;
                $incidence->comments = json_encode($body);
                $incidence->client = $answer->client;
                $incidence->store = $answer->store;
                $incidence->order = json_encode($ot);
                $incidence->token = Str::random(8);
    
                $incidence->save();

                $agent = Agent::find($task[0]);
                $body = [];
                $body = [
                    'responsable' => auth()->user()->name,
                    'owner' => $agent,
                    'impact' => $request['impact'][$index],
                    'token' => $incidence->token,
                    'ot' => $ot,
                    'id' => $incidence->id,
                    'comment' => $request['incidence'][$index],
                    'new' => true
                ];

                Mail::to($agent['email'])->send(new NotifyMail($body));

                $body = null;
            }
        }


        return redirect()->route('tasks')->with('success','Task Complete!');

    }
}
