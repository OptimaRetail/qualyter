<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index() {
        $clients = Client::all();

        return view('admin.client.index',['clients' => $clients]);
    }

    public function new() {
        return view('admin.client.profile', ['client' => null]);
    }

    public function create(Request $request) {
        $validated = $request->validate([
            'name' => 'required',
            'country' => 'required',
            'email' => ['required','min:6'],
        ]);

        $url = 'https://restcountries.com/v2/name/'.$request->get('country');

        $curl = curl_init($url);
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

        $resp = curl_exec($curl);
        curl_close($curl);
        $resp = json_decode($resp);
        
        $client = new Client;
        $client->name = $request->get('name');
        $client->phonenumber = $request->get('phonenumber');
        $client->email = $request->get('email');
        $client->delegation = $resp[0]->alpha2Code;
        $client->language = $resp[0]->languages[0]->iso639_1;

        $client->save();
        
        return redirect()->route('clients')->with('success','Client added successfuly!');
    }

    public function edit($id) {
        $client = Client::find($id);

        return view('admin.client.profile',['client' => $client]);
    }

    public function update(Request $request, $id) {

    }
}
