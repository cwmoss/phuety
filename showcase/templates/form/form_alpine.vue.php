<form x-data="form()" x-validatetable="{lazy:true}" x-cloak novalidate :action="action" method="POST">
    <slot.></slot.>
</form>

<script head type="module" src="/assets/form.js"></script>
<script head src="//unpkg.com/alpinejs" defer></script>