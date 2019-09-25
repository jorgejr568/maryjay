@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">Criar dashboard</div>

                    <div class="card-body">
                        <create-dashboard :queries="{{json_encode($queries)}}"></create-dashboard>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
