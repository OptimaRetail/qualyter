<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Answer;
use App\Models\Team;
use App\Models\Agent;

class ApiController extends Controller
{
    public function answers(Request $request) {
        $question = json_decode($request->getContent(),true);
        
        $answers = DB::table('answers')->select('id', DB::raw('count(*) as total'));

        $answers = $this->dating($question,$answers);

        if(isset($question['status'])) {
            if(gettype($question['status'])=='array') {
                $answers->whereIn('status',$question['status']);
            } else {
                $answers->where('status','=',$question['status']);
            }
        }

        if(isset($question['filter'])) {
            if(gettype($question['filter'])=='array') {
                foreach($question['filter'] as $index => $value) {
                    switch($index) {
                        case 'agent':
                            if(gettype($value)=='array') {
                                $answers->whereIn('owner',$value);
                            } else {
                                $answers->where('owner','=',$value);
                            }
                            break;
                        case 'team': 
                            $id = $this->getAgents($value);
                            $answers->whereIn('owner',$value);
                            break;
                        case 'client':
                            if(gettype($value)=='array') {
                                $answers->whereIn('client',$value);
                            } else {
                                $answers->where('client','=',$value);
                            }
                            break;
                        case 'store':
                            if(gettype($value)=='array') {
                                $answers->whereIn('store',$value);
                            } else {
                                $answers->where('store','=',$value);
                            }
                            break;
                    }
                }
            }
        }

        if(isset($question['group'])) {
            foreach($question['group'] as $group) {
                $answers->groupBy($group);
            }
        }

        $answers = $answers->get();
        
        return json_encode($answers);

    }

    public function emails(Request $request) {
        $body = json_decode($request->getContent(), true);
        
        $answers = Answer::whereIn('status',[3,4]);

        $answers = $this->dating($body,$answers);

        $answers = $answers->get();

        $res = [];
        $res['total'] = count($answers);

        if($body['not_respond']) {
            $responds = Answer::where('status','=',3);
            $responds = $this->dating($body,$answers);
            $res['not_respond'] = count($responds);
        }
        foreach($answers as $answer) {
            $res['body'][] = $answer;
        }

        echo json_encode($res);
    }

    private function dating($body,$elements)  {
        if($body['start_date'] != null) {
            //tenemos fecha inicio
            if($body['end_date'] != null) {
                //tenemos fechas
                $elements->whereBetween('expiration',[$body['start_date'],$body['end_date']]);
            } else {
                //hasta fecha actual
                $elements->where('expiration','<=',$body['start_date']);
            }
        } else {
            if($body['end_date'] != null) {
                //tenemos fecha final
                $elements->where('expiration','>=',$body['end_date']);
            }
        }

        return $elements;
    }

    private function getAgents($team) {
        if(gettype($team)=='array') {
            $agents = Agent::whereIn('team',$team)->get();
        } else {
            $agents = Agent::where('team','=',$team)->get();
        }

        $id = [];
        foreach($agents as $agent) {
            $id[] = $agent->id;
        }

        return $id;
    }
}
