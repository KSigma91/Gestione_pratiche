<!-- pulsante fisso in basso -->
<style>
.activity-log-button {
    position: fixed;
    right: 18px;
    bottom: 18px;
    z-index: 1100;
    border-radius: 50%;
    width: 56px;
    height: 56px;
    display:flex;
    align-items:center;
    justify-content:center;
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);
}
</style>

<button id="openActivityLog" class="btn btn-info activity-log-button" title="Visualizza log attività" type="button">
    <i class="fas fa-history"></i>
</button>

<!-- Modal Bootstrap 5 -->
<div class="modal fade" id="activityLogModal" tabindex="-1" aria-labelledby="activityLogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="activityLogModalLabel">Log attività</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body" id="activityLogModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border" role="status"><span class="visually-hidden">Caricamento...</span></div>
                    <div class="mt-2">Caricamento attività…</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var openBtn = document.getElementById('openActivityLog');
    var modalEl = document.getElementById('activityLogModal');
    var modalBody = document.getElementById('activityLogModalBody');

    if (!openBtn || !modalEl || !modalBody) return;

    var bsModal = new bootstrap.Modal(modalEl, {});
    var currentFetchController = null;

    // spinner HTML (riutilizzabile)
    function spinnerHtml() {
        return '<div class="text-center py-4">' +
               '  <div class="spinner-border" role="status"><span class="visually-hidden">Caricamento...</span></div>' +
               '  <div class="mt-2">Caricamento attività…</div>' +
               '</div>';
    }

    // apri modal e carica partial via AJAX
    openBtn.addEventListener('click', function () {
        // se c'è una fetch precedente la annullo per sicurezza
        if (currentFetchController) {
            try { currentFetchController.abort(); } catch(e) {}
            currentFetchController = null;
        }
        currentFetchController = new AbortController();

        // mostra modal subito con spinner
        modalBody.innerHTML = spinnerHtml();
        bsModal.show();

        // fetch del partial (assicurati che la route sia corretta)
        fetch('{{ route("admin.logs.partial") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            },
            credentials: 'same-origin',
            signal: currentFetchController.signal
        }).then(function (res) {
            if (!res.ok) throw new Error('Errore rete: ' + res.status);
            return res.text();
        }).then(function (html) {
            // inserisci contenuto solo se il modal è ancora aperto
            modalBody.innerHTML = html;
        }).catch(function (err) {
            if (err.name === 'AbortError') {
                // fetch annullata volontariamente: non fare nulla
                return;
            }
            console.error('Errore fetch log:', err);
            modalBody.innerHTML = '<div class="alert alert-danger">Errore nel caricamento dei log: ' + (err.message || '') + '</div>';
        }).finally(function () {
            currentFetchController = null;
        });
    });

    // Se il modal viene chiuso, assicuriamoci di pulire qualsiasi cosa rimasta
    modalEl.addEventListener('hidden.bs.modal', function () {
        // annulla fetch in corso (se esiste)
        if (currentFetchController) {
            try { currentFetchController.abort(); } catch(e) {}
            currentFetchController = null;
        }

        // Forza rimozione di tutti gli overlay/backdrop residui
        document.querySelectorAll('.modal-backdrop').forEach(function(el){
            try { el.parentNode && el.parentNode.removeChild(el); } catch(e){}
        });

        // Rimuove la classe che blocca lo scroll (se presente)
        document.body.classList.remove('modal-open');

        // svuota il body del modal (per evitare markup duplicato al prossimo open)
        modalBody.innerHTML = '';
    });

    // Protezione extra: se ci sono errori JS non gestiti, rimuoviamo overlay alla pagina unload
    window.addEventListener('beforeunload', function () {
        document.querySelectorAll('.modal-backdrop').forEach(function(el){
            try { el.parentNode && el.parentNode.removeChild(el); } catch(e){}
        });
        document.body.classList.remove('modal-open');
    });
});
</script>
@endpush

