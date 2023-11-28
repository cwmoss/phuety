<form v-else x-data="form()" x-validatetable="{lazy:true}" x-cloak novalidate :action="action" method="POST">
    <slot></slot>
</form>

<script header type="module" src="/assets/form.js"></script>
<script header src="//unpkg.com/alpinejs" defer></script>