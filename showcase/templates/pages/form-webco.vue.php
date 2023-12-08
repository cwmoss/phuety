<layout title="Contact Us" :path="props.path">

    <h1>You can contact us via this form</h1>

    <form-webco success="Thank you!">
        <form action="/contact" method="POST">
            <form-field name="name" label="Name" :value="input.name" :error="errors.name"></form-field>
            <form-field name="email" label="eMail Address" :value="input.email" :error="errors.email"></form-field>
            <form-field name="found_via" label="How Do You Know Us" :value="input.found_via" :error="errors.found_via" type="select" :options="via"></form-field>
            <button type="submit">Send It To Us</button>
        </form>
    </form-webco>
</layout>