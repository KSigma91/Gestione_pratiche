@extends('layouts.navbar')

@section('content')
<div class="container">
    <div class="d-flex justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Archivio pratiche</h3>
                <a class="btn btn-secondary" href="{{ route('admin.pratiche.index') }}"><i class="fas fa-arrow-left me-1"></i> Torna alla lista</a>
            </div>
            <ul class="list-group">
                @php
                    $grouped = $archive->groupBy('year');
                @endphp

                @foreach($grouped as $year => $months)
                    <li class="list-group-item">
                        <h5>{{ $year }}</h5>
                        <ul class="list-inline">
                            @foreach($months as $item)
                                @php
                                    $m = $item->month;
                                    $count = $item->total;
                                    $monthName = \Carbon\Carbon::create()->month($m)->translatedFormat('F');
                                @endphp
                                <li class="list-inline-item me-3">
                                    <a href="{{ route('admin.pratiche.archive.view', ['year' => $year, 'month' => $m]) }}">
                                        {{ $monthName }} ({{ $count }})
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
