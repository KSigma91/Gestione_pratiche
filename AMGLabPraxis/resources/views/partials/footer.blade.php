
{{-- resources/views/partials/footer.blade.php --}}
<footer class="site-footer d-none d-lg-block" role="contentinfo" aria-label="Footer del sito">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-start mt-3 ms-2">
            <div class="footer-text small text-muted">Realizzato da: <a href="https://amglablecce.it" class="text-decoration-none "><strong class="text-secondary fs-5" style="font-family: 'Markazi Text'">
                 AMG Lab</strong></a>
            </div>
        </div>
    </div>
</footer>

<style>
    /* Footer fisso in basso */
:root {
    --site-footer-height: 56px; /* altezza footer (modifica se vuoi più alto) */
}

/* spazio sotto il main per non essere coperto dal footer */
main, .main-content {
    padding-bottom: calc(var(--site-footer-height) + 1rem); /* 1rem di margine addizionale */
}

/* footer base */
.site-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    width: 190px;
    height: var(--site-footer-height);
    z-index: 1050;
    display: block;
}

/* mobile tweaks: riduci altezza e logo su schermi piccoli */
@media (max-width: 576px) {
    :root { --site-footer-height: 52px; }
    main, .main-content { padding-bottom: calc(var(--site-footer-height) + 0.75rem); }
}
</style>
