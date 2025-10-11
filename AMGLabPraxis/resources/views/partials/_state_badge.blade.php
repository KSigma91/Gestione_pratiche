@php
    $map = [
        'in_giacenza' => ['label'=>'In Giacenza','class'=>'bg-warning text-dark','icon'=>'fa-clock'],
        'in_lavorazione' => ['label'=>'In Lavorazione','class'=>'bg-info text-dark','icon'=>'fa-spinner'],
        'completata' => ['label'=>'Completata','class'=>'bg-success text-white','icon'=>'fa-check'],
        'annullata' => ['label'=>'Annullata','class'=>'bg-danger text-white','icon'=>'fa-ban'],
    ];
    $s = $map[$stato] ?? ['label'=>ucfirst($stato),'class'=>'bg-secondary text-white','icon'=>'fa-question'];
@endphp

<span class="badge {{ $s['class'] }}" style="font-size: .7rem">
    <i class="fas {{ $s['icon'] }} me-1"></i> {{ $s['label'] }}
</span>
