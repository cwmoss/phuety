<div class="form-field" :class="{'has-error':error}">
    <label :for="name">{{label}}</label>

    <input :if="type=='text'" type="text" :id="name" :name="name" :value="value">

    <select :if="type=='select'" :id="name" :name="name">

        <option :foreach="option in options" :value="option" :selected="option==value">{{option}}</option>
    </select>

    <div :if="error" class="invalid-feedback">{{error}}</div>
</div>


<style>
    root {
        margin-bottom: 1em;

    }

    .invalid-feedback {
        color: red;
    }

    input+.invalid-feedback {
        margin-top: -1em;
    }
</style>

<?php

$type = $props->type ?? "text";

// print_r($props['options']);
