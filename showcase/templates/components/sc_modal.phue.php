<cta-modal>

    <div slot="button">
        <button class="cta-modal-toggle open" type="button">{{open}}</button>
    </div>

    <div slot="modal">
        <h2 :if="title">{{title}}</h2>
        <slot.></slot.>
        <p>
            <button class="cta-modal-toggle" type="button">{{close}}</button>
        </p>
    </div>

</cta-modal>

<script head src="/assets/cta-modal.js"></script>

<style>
    .cta-modal-toggle.open {
        padding: 0 1em;
    }
</style>
<?php
/*
https://www.smashingmagazine.com/2022/04/cta-modal-build-web-component/
https://host.sonspring.com/cta-modal/
*/
$title = $props->title ?? '';
$close = $props->close ?? 'Close';
$open = $props->open ?? 'Open';
