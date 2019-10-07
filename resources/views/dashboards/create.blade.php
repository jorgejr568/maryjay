@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">Criar dashboard</div>

                    <div class="card-body">
                        @if($errors->any())
                            @foreach($errors->all() as $error)
                                <div class="row">
                                    <div class="col">
                                        <div class="alert alert-danger">
                                            {{$error}}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        <form action="{{route('dashboards.store')}}" method="post">
                            <create-dashboard :queries="{{json_encode($queries)}}"></create-dashboard>
                            {{csrf_field()}}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
