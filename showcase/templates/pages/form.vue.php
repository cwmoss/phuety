<layout title="Contact Us" :path="props.path">

    <h1>You can contact us via this form</h1>

    <section v-if="success">
        <aside>
            <h4>Thank you for your message!</h4>
        </aside>
    </section>

    <form v-else action="/contact" method="POST">

        <form-field name="name" label="Name" :value="input.name" :error="errors.name"></form-field>
        <form-field name="email" label="eMail Address" :value="input.email" :error="errors.email"></form-field>
        <form-field name="found_via" label="How Do You Know Us" :value="input.found_via" :error="errors.found_via" type="select" :options="via"></form-field>
        <button type="submit">Send</button>

    </form>

</layout>

<style>
    h1 {
        color: gold;
    }
</style>

<?php

$success = false;
