@extends('layouts.dashboard')

@section('content')
<div class="mb-3">
    <h1 class="h3 d-inline align-middle">@if ($role != null) {{__('Edit Role')}} @else {{__('Create New Role')}} @endif </h1>
</div>
<div class="row">
    <form method="POST" action="@if ($role != null) {{route('roles.update',['id'=>$role->id])}} @else {{route('roles.create')}} @endif">
        @if ($role != null) 
            @method('PUT')
        @endif
        @csrf
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{__('Public info')}}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">{{__('Name')}}</label>
                        <input type="text" class="form-control" id="name" name="name" @if ($role != null) value="{{$role->name}}" @endif/>
                    </div>
                    <div class="mb-3">
                        <label for="slug" class="form-label">{{__('Slug')}}</label>
                        <input type="text" class="form-control" id="slug" name="slug" @if ($role != null) value="{{$role->slug}}" @endif />
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{__('Permissions')}}</h5>
                </div>
                <div class="card-body">
                    <div>
                    @foreach ($permissions as $permission)
                        <label for="permission[]" class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" id="permission[]" name="permission[]" value="{{$permission->slug}}" @if ($role_permission != null) @foreach ($role_permission as $r_p) @if($r_p->slug == $permission->slug) checked @endif @endforeach @endif/>
                            <span class="form-check-label">{{$permission->name}}</span>
                        </label>
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="d-grid gap-2">
            <button class="btn btn-success">{{__('Create Role')}}</button>
        </div>
    </form>
</div>
@endsection