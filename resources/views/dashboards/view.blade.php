@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h2>{{ $dashboard->name }}</h2>

                @if($dashboard->infos())
                    <div class="row">
                        <div class="col-4">
                            <div class="card">
                                <div class="card-header">Palavras mais utilizadas</div>

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-roll table-bordered table-hover">
                                            <tbody>
                                            @foreach($dashboard->infos()->most_user_words as $word)
                                                <tr>
                                                    <td>{{$word->word}}</td>
                                                    <td>{{$word->count}}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card">
                                <div class="card-header">Tweets mais retweetados</div>

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-roll table-bordered table-hover">
                                            <tbody>
                                            @foreach($dashboard->infos()->most_rt_status as $status)
                                                <tr>
                                                    <td>{{$status->text}}</td>
                                                    <td>{{$status->rt}}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card">
                                <div class="card-header">Tweets mais favoritados</div>

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-roll table-bordered table-hover">
                                            <tbody>
                                            @foreach($dashboard->infos()->most_favorite_status as $status)
                                                <tr>
                                                    <td>{{$status->text}}</td>
                                                    <td>{{$status->favorite}}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-5">
                        <div class="col-4">
                            <div class="card">
                                <div class="card-header">Lugares onde mais tweetam</div>

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-roll table-bordered table-hover">
                                            <tbody>
                                            @foreach($dashboard->infos()->most_used_locations as $location)
                                                <tr>
                                                    <td>{{$location->location}}</td>
                                                    <td>{{$location->count}}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card">
                                <div class="card-header">Usuários com mais seguidores</div>

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-roll table-bordered table-hover">
                                            <tbody>
                                            @foreach($dashboard->infos()->most_followed_users as $user)
                                                <tr>
                                                    <td>&#64;{{$user->screenName}}</td>
                                                    <td>{{$user->followers}}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card">
                                <div class="card-header">Usuários mais favoritados</div>

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-roll table-bordered table-hover">
                                            <tbody>
                                            @foreach($dashboard->infos()->most_favorites_users as $user)
                                                <tr>
                                                    <td>&#64;{{$user->screenName}}</td>
                                                    <td>{{$user->favorite}}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
