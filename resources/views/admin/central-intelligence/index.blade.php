@extends('layouts.app')

@section('page-title', __('Central Intelligence'))

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="card card-stats">
            <div class="card-body">
                <h6>{{ __('Total Fraud Records') }}</h6>
                <h3>{{ $totalFraudChecks }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stats">
            <div class="card-body">
                <h6>{{ __('Total Blocked Users') }}</h6>
                <h3>{{ $totalBlocked }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5>{{ __('Intelligence Search') }}</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="d-flex">
            <input type="text" name="q" class="form-control" placeholder="Search phone number..." value="{{ $query }}">
            <button type="submit" class="btn btn-primary ml-2">Search</button>
        </form>
        @if($query)
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>Phone</th>
                    <th>Incidents</th>
                    <th>Last Seen (Store)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $res)
                <tr>
                    <td>{{ $res->phone }}</td>
                    <td>{{ $res->total_reported_incidents }}</td>
                    <td>{{ $res->last_reported_store_id }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $results->appends(['q' => $query])->links() }}
        @endif
    </div>
</div>
@endsection
