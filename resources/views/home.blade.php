@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Aquisições</div>

                <div class="card-body text-center">
                    <h2>Total</h2>
                    <h3 class="badge badge-success" style="font-size: 25px">
                        {{ number_format($acquisitions['total'],0,'.','.') }}
                    </h3>

                    <hr>

                    <h2>Em processamento</h2>
                    <h3 class="badge badge-warning" style="font-size: 25px">
                        {{ number_format($acquisitions['total'] - $acquisitions['processed'],0,'.','.') }}
                    </h3>

                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Queries</div>

                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Query</th>
                            <th>N. tweets</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($acquisitions['per_query'] as $per_query)
                                <tr>
                                    <td>{{ $per_query->query }}</td>
                                    <td>{{ number_format($per_query->count,0,'.','.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
