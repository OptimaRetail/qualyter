@extends('layouts.dashboard')

@section('content')
<div class="btn-group" role="group" aria-label="Basic outlined example">
  <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filters"><i class="align-middle" data-feather="filter"></i></button>
</div>
<div class="d-grid gap-2 d-md-flex justify-content-md-end mb-3">
    <a class="btn btn-primary" href="{{route('workorder.new')}}">{{__('Add Work Order')}}</a>
</div>

<!-- Start Filter -->
<form method="GET">
<div class="modal fade" id="filters" data-bs-backdrop="static" data-bs-keyboard="false" tabinde="-1" aria-labelledby="filterLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterLabel">{{__("Filters")}}</h5>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="store" class="form-label">{{__('Store`s Name')}}</label>
                                <input type="text" class="form-control" name="store" placeholder="{{__('Store`s Name')}}" @if(!empty($filters) && isset($filters['store'])) value="{{$filters['store']}}" @endif/>
                            </div>
                            <div class="mb-3">
                                <label for="client" class="form-label">{{__('Client`s Name')}}</label>
                                <input type="text" class="form-control" name="client" placeholder="{{__('Client`s Name')}}" @if(!empty($filters) && isset($filters['client'])) value="{{$filters['client']}}" @endif/>
                            </div>
                            <div class="mb-3">
                                <label for="client" class="form-label">{{__("Work Order")}}</label>
                                <input type="text" class="form-control" name="workOrder" placeholder="{{__('Work Order')}}"  @if(!empty($filters) && isset($filters['workOrder'])) value="{{$filters['workOrder']}}" @endif/>
                            </div>
                            <div class="mb-3">
                                <label for="agent" class="form-label">{{__('Agent')}}</label>
                                <input class="form-control" list="agents" id="agent" name="agent" placeholder="{{__('Type to search...')}}" @if(!empty($filters) && isset($filters['agent'])) value="{{$filters['agent']}}" @endif/>
                                <datalist id="agents">
                                    @foreach($agents as $agent)  
                                        <option value="{{$agent->id}}">{{$agent->name}}</option>
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>{{__('Created')}}:</label>
                            <div class="input-group mb-3">
                                <input type="date" name="start_date_created" class="form-control" aria-label="{{__('Start date')}}" @if(!empty($filters) && isset($filters['start_date_created'])) value="{{$filters['start_date_created']}}" @endif>
                                <span class="input-group-text"><-></span>
                                <input type="date" name="end_date_created" class="form-control" aria-label="{{__('End date')}}" @if(!empty($filters) && isset($filters['end_date_created'])) value="{{$filters['end_date_created']}}" @endif>
                            </div>
                            <label>{{__('Closing day')}}:</label>
                            <div class="input-group mb-3">
                                <input type="date" name="start_date_closing" class="form-control" aria-label="{{__('Start date')}}" @if(!empty($filters) && isset($filters['start_date_closing'])) value="{{$filters['start_date_closing']}}" @endif>
                                <span class="input-group-text"><-></span>
                                <input type="date" name="end_date_closing" class="form-control" aria-label="{{__('End date')}}" @if(!empty($filters) && isset($filters['end_date_closing'])) value="{{$filters['end_date_closing']}}" @endif>
                            </div>
                        </div>
                        <div class="col-md-4">

                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__("Cancel")}}</button>
                <button type="submit" class="btn btn-primary">{{__("Search")}}</button>
            </div>
        </div>
    </div>
</div>
</form>
<!-- End Filter -->

<table class="table table-hover table-striped">
    <thead>
        <tr>
            <th scope="col">@sortablelink('store',__('Store'))</th>
            <th scope="col">@sortablelink('client',__('Client'))</th>
            <th scope="col">@sortablelink('expiration',__('Expiration'))</th>
            <th scope="col"></th>
        </tr>
    </thead>
    <tbody>
        @foreach($answers as $answer)
            @foreach($stores as $store)
                @if($store->code == $answer->store && $store->client == $answer->client)
                    @if($store->phonenumber==null && $store->email==null)
                    <tr class="table-dark">
                    @else
                        @switch($answer->status)
                            @case(0) <tr class="table-success"> @break
                            @case(1) <tr class="table-warning"> @break
                            @case(3) <tr class="table-danger"> @break
                        @endswitch
                    @endif
                        <td>
                            {{$store->name}}
                        </td>
                        <td>
                            @foreach($clients as $client)
                                @if($client->id == $answer->client)
                                    {{$client->name}}
                                @endif
                            @endforeach
                        </td>
                        <td>
                            {{$answer->expiration}}
                        </td>
                        <td>
                    @if($store->phonenumber!=null)
                        <a href="{{route('tasks.view',['id'=>$answer->id])}}" class="btn btn-outline-primary @if($answer->user != $id && $answer->user != null) disabled @endif"><i class="align-middle" data-feather="phone"></i></a>
                    @elseif($store->email!=null)
                        <a href="{{route('tasks.notrespond', ['id'=>$answer->id])}}" class="btn btn-outline-primary @if($answer->user != null && $answer->user != $id) disabled @endif"><i class="align-middle" data-feather="send"></i></a>
                    @else 
                        <?php $id = str_replace('/','_',$store->code); ?>
                        <a href="{{route('stores.edit',['id'=>$id])}}" class="btn btn-outline-warning"><i class="align-middle" data-feather="edit"></i></a>
                    @endif
                        </td>
                    </tr>
                @endif
            @endforeach
        @endforeach
    </tbody>
</table>
{{$answers->appends([
        'filters' => $filters,
        'filtered' => 'yes'
    ])->links()}}
@endsection