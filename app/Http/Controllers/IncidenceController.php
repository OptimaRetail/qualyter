<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Incidence;
use App\Models\Store;
use App\Models\Client;
use App\Models\User;
use App\Models\Task;
use App\Models\Log;
use App\Models\Team;
use Illuminate\Http\Request;
use Mail;
use Carbon\Carbon;
use App\Mail\NotifyMail;
use Illuminate\Support\Str;

class IncidenceController extends Controller
{
    public function index(Request $request) {
        $filters = $request->query();
        if(isset($filters['filtered']) && isset($filters['filters'])) {
            $filters = $filters['filters'];
        }
        $incidences = Incidence::query();

        /* Start filters */
        if(!empty($filters['store']) && $filters['store'] != '') {
            $id = [];
            $stores = Store::where('name','LIKE','%'.$filters['store'].'%')->get();
            foreach($stores as $s) {
                $id[] = $s->code;
            }
            $incidences->whereIn('store',$id);
        }

        if(!empty($filters['client']) && $filters['client'] != '') {
            $id = [];
            $clients = Client::where('name','LIKE','%'.$filters['client'].'%')->get();
            foreach($clients as $c) {
                $id[] = $c->id;
            }
            $incidences->whereIn('client',$id);
        }

        if(!empty($filters['agent']) && $filters['agent'] != '') {
            $incidences->where('owner','=',$filters['agent']);
        }

        if(!empty($filters['responsable']) && $filters['responsable'] != '') {
            $incidences->where('responsable','=',$filters['responsable']);
        }

        if(!empty($filters['start_date_created']) && $filters['start_date_created'] != '') {
            if(!empty($filters['end_date_created']) && $filters['end_date_created'] != '') {
                $incidences->whereBetween('created_at',[$filters['start_date_created'],$filters['end_date_created']]);
            } else {
                $incidences->where('created_at','>=',$filters['start_date_created']);
            }
        } else {
            if(!empty($filters['end_date_created']) && $filters['end_date_created'] != '') {
                $incidences->where('created_at','<=',$filters['end_date_created']);
            }
        }

        if(!empty($filters['start_date_closed']) && $filters['start_date_closed'] != '') {
            if(!empty($filters['end_date_closed']) && $filters['end_date_closed'] != '') {
                $incidences->whereBetween('updated_at',[$filters['start_date_closed'],$filters['end_date_closed']]);
            } else {
                $incidences->where('updated_at','>=',$filters['start_date_closed']);
            }
        } else {
            if(!empty($filters['end_date_closed']) && $filters['end_date_closed'] != '') {
                $incidences->where('updated_at','<=',$filters['end_date_closed']);
            }
        }

        if(!empty($filters['start_date_closing']) && $filters['start_date_closing'] != '') {
            if(!empty($filters['end_date_closing']) && $filters['end_date_closed'] != '') {
                $incidences->whereBetween('closed',[$filters['start_date_closing'],$filters['end_date_closing']]);
            } else {
                $incidences->where('closed','>=',$filters['start_date_closing']);
            }
        } else {
            if(!empty($filters['end_date_closing']) && $filters['end_date_closing'] != '') {
                $incidences->where('closed','<=',$filters['end_date_closing']);
            }
        }

        if(!empty($filters['status'])) {
            $status = [];
            foreach($filters['status'] as $index => $value) {
                if($value=='true') {
                    $status[] = $index;
                }
            }
            $incidences->whereIn('status',$status);
        }

        if(!empty($filters['impact'])) {
            $impact = [];
            foreach($filters['impact'] as $index => $value) {
                if($value=='true') {
                    $impact[] = $index;
                }
            }
            $incidences->whereIn('impact',$impact);
        }
        if(!empty($filters['team']) && $filters['team'] != '') {
            $id=[];
            $agents = Agent::where('team','=',$filters['team'])->get();
            foreach($agents as $agent) {
                $id[] = $agent->id;
            }
            $incidences->whereIn('owner',$id);
        }

        /* End filters */

        $rol = auth()->user()->roles;
        $rol = json_decode($rol[0]);
        if($rol->id == 2) {
            $id = [];
            $teams = Team::where('manager','=',auth()->user()->id)->get();
            foreach($teams as $team) {
                $url[] = $team->url;
            }
            $agents = Agent::whereIn('team',$url)->get();
            foreach($agents as $agent) {
                $id[] = $agent->id;
            }
            $incidences->whereIn('owner',$id);
        }

        if(isset($filters['sort'])) {
            $incidences = $incidences->sortable()->paginate(10);
        } else {
            $incidences = $incidences->orderBy('created_at','DESC')->paginate(10);
        }

        $stores = Store::all();
        $users = User::all();
        $agents = Agent::all();
        $stores = Store::all();
        $clients = Client::all();
        $teams = Team::all();
        $tasks = Task::all();

        return view('admin.incidence.index', ['incidences' => $incidences, 'tasks' => $tasks, 'stores' => $stores, 'users' => $users, 'agents' => $agents, 'clients' => $clients, 'stores' => $stores, 'filters' => $filters, 'teams' => $teams]);
    }

    public function view($id) {
        $incidence = Incidence::find($id);
        $store = Store::where('code','=',$incidence->store)->get();
        $user = User::find($incidence->responsable);
        $agent = Agent::find($incidence->owner);
        $order = json_decode($incidence->order);
        $comments = json_decode($incidence->comments);
        $agents = Agent::all();
        $owner = auth()->user();

        return view('admin.incidence.view', ['incidence' => $incidence, 'store' => $store[0], 'user' => $user, 'agent' => $agent, 'order' => $order, 'comments' => $comments, 'agents' => $agents, 'owner' => $owner]);
    }

    public function create(Request $request) {
        $filters = $request->query();
        if(isset($filters['filtered']) && isset($filters['filters'])) {
            $filters = $filters['filters'];
        }

        $body = null;
        $body[0]['message'] = $request['message'];
        $body[0]['owner'] = auth()->user()->name;
        $body[0]['type'] = 'user';

        $store = Store::where('code','=',$request['store'])->get();

        $incidence = new Incidence();

        $incidence->responsable = auth()->user()->id;
        $incidence->owner = $request['agent'];
        $incidence->impact = $request['impact'];
        $incidence->status = 0;
        $incidence->comments = json_encode($body);
        $incidence->client = $store[0]->client;
        $incidence->store = $request['store'];
        $incidence->order = $request['task'];
        $incidence->token = Str::random(8);
        $incidence->closed = $request['control'];

        $incidence->save();

        return view('admin.incidence.index', ['filters' => $filters])->with('success', 'Incidence created!');
    }

    public function changeAgent($id, Request $request) {
        $incidence = Incidence::find($id);
        $incidence->owner = $request->get('agent');
        $incidence->save();

        return redirect()->to('/incidences/'.$id)->with('success','Agent changed!');
    }

    public function complete($id) {
        $log = new AuditionController();
        $incidence = Incidence::find($id);
        $old_incidence = Incidence::find($id);

        $incidence->status = 4;
        if($old_incidence->status != $incidence->status) {
            $log->saveLog($old_incidence,$incidence,'i');
        }

        $incidence->updated_at = Carbon::now();

        $incidence->save();

        return redirect()->to('/incidences')->with('success','Incident closed, thank you for your cooperation.'); 
    }

    public function modify($id, Request $request) {
        $log = new AuditionController();
        $incidence = Incidence::find($id);
        $old_incidence = Incidence::find($id);

        if($request->get('status') != null) {
            if($incidence->status != null) {
                $incidence->status = $request->get('status');
            }
        }
        

        $incidence->closed = $request->get('closed');

        $comments = json_decode($incidence->comments);

        if($request->get('status')!=4) {
            if($request->get('message') == null) {
                $body_message['message'] = 'Modify control day: '.$incidence->closed;
            } else {
                $body_message['message'] = $request->get('message');
            }
            $body_message['owner'] = auth()->user()->name;
            $body_message['type'] = 'user';
            $body_message['date'] = Carbon::now();
    
            $comments[] = $body_message;
    
            $incidence->comments = json_encode($comments);
        }

        $incidence->save();

        if($old_incidence->status != $incidence->status) {
            $log->saveLog($old_incidence,$incidence,'i');
        }

        $ot = json_decode($incidence->order);
        $messages = json_decode($incidence->comments);
        $agent = Agent::find($incidence->owner);

        $body = [
            'responsable' => $incidence->responsable,
            'owner' => $agent,
            'impact' => $incidence->impact,
            'token' => $incidence->token,
            'ot' => $ot,
            'id' => $incidence->id,
            'comment' => $messages[count($messages)-1]->message,
            'new' => false
        ];

        if(env('APP_NAME')=='QualyterTEST') {
            Mail::to('test@optimaretail.es')->send(new NotifyMail($body));
        } else {
            Mail::to($agent['email'])->send(new NotifyMail($body));
        }

        return redirect()->to('/incidences')->with('success','Notify agent in this moment');
    }

    public function response($id, Request $request) {
        $incidence = Incidence::find($id);

        if($incidence->token != $request->get('code')) {
            abort(403, 'Unauthorized action.');
        }

        $store = Store::where('code','=',$incidence->store)->get();
        $user = User::find($incidence->responsable);
        $agent = Agent::find($incidence->owner);
        $order = json_decode($incidence->order);
        $comments = json_decode($incidence->comments);
        $agents = Agent::all();

        return view('public.agent', ['incidence' => $incidence, 'store' => $store[0], 'user' => $user, 'agent' => $agent, 'order' => $order, 'comments' => $comments, 'agents' => $agents]);
    }

    public function update($id, Request $request) {
        $log = new AuditionController();
        $incidence = Incidence::find($id);
        $old_incidence = Incidence::find($id);

        $incidence->status = 1;
        $incidence->closed = $request->get('closed');

        $comments = json_decode($incidence->comments);

        $body_message['message'] = $request->get('message');
        $body_message['owner'] = $request->get('agent');
        $body_message['type'] = 'agent';
        $body_message['date'] = Carbon::now();

        $comments[] = $body_message;

        $incidence->comments = json_encode($comments);

        $incidence->save();

        $log = new Log();
        $log->table = 'a';
        $log->row_id = $id;
        $log->old = $old_incidence->status;
        $log->new = 1;
        $log->created = date('Y-m-d H:i');
        $log->save();
        $ot = json_decode($incidence->order);
        $messages = json_decode($incidence->comments);
        $agent = Agent::find($incidence->owner);

        $user = User::find($incidence->responsable);

        $body = [
            'responsable' => $incidence->responsable,
            'owner' => $agent,
            'impact' => $incidence->impact,
            'token' => $incidence->token,
            'ot' => $ot,
            'id' => $incidence->id,
            'comment' => $messages[count($messages)-1]->message,
            'new' => false
        ];

        if(env('APP_NAME')=='QualyterTEST') {
            Mail::to('test@optimaretail.es')->send(new NotifyMail($body));
        } else {
            Mail::to($user['email'])->send(new NotifyMail($body));
        }

        return view('public.thanksAgents');
    }

    public function resend($id) {
        $incidence = Incidence::find($id);
        
        $ot = json_decode($incidence->order);
        $messages = json_decode($incidence->comments);
        $agent = Agent::find($incidence->owner);

        $body = [
            'responsable' => $incidence->responsable,
            'owner' => $agent,
            'impact' => $incidence->impact,
            'token' => $incidence->token,
            'ot' => $ot,
            'id' => $incidence->id,
            'comment' => $messages[0]->message,
            'new' => true
        ];

        if(env('APP_NAME')=='QualyterTEST') {
            Mail::to('test@optimaretail.es')->send(new NotifyMail($body));
        } else {
            Mail::to($agent['email'])->send(new NotifyMail($body));
        }

        return redirect()->to('/incidences/'.$id)->with('success','Incidence sended!');
    }
}
