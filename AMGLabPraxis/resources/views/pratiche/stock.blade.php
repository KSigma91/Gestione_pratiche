@extends('layouts.navbar')

@section('content')
<div class="giacenza-container">
    <span class="giacenza-label" data-bs-toggle="tooltip" title="Visualizza pratiche in giacenza">
        Giacenza
    </span>
    <div class="giacenza-dropdown">
        @foreach($praticheGiacenza as $pratica)
            <a href="{{ route('pratica.show', $pratica->id) }}" class="giacenza-item" data-bs-toggle="tooltip" title="Visualizza dettagli">
                {{ $pratica->codice }} - {{ $pratica->descrizione }}
            </a>
        @endforeach
    </div>
</div>

{{ $pratiche->links() }}

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tooltipElements = document.querySelectorAll('[data-toggle="tooltip"]');
        tooltipElements.forEach(function (element) {
            element.addEventListener('mouseenter', function () {
                const tooltipText = element.getAttribute('title');
                const tooltip = document.createElement('div');
                tooltip.classList.add('tooltip');
                tooltip.innerText = tooltipText;
                document.body.appendChild(tooltip);

                const rect = element.getBoundingClientRect();
                tooltip.style.left = `${rect.left + rect.width / 2 - tooltip.offsetWidth / 2}px`;
                tooltip.style.top = `${rect.top - tooltip.offsetHeight - 5}px`;

                element.addEventListener('mouseleave', function () {
                    tooltip.remove();
                });
            });
        });
    });
</script>

<style>
    .giacenza-container {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }

    .giacenza-label {
        font-weight: bold;
        color: #007bff;
    }

    .giacenza-dropdown {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        min-width: 200px;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
        z-index: 1;
    }

    .giacenza-item {
        display: block;
        padding: 8px 16px;
        color: #333;
        text-decoration: none;
    }

    .giacenza-item:hover {
        background-color: #ddd;
    }

    .giacenza-container:hover .giacenza-dropdown {
        display: block;
    }
</style>
@endsection
