<div class="form-field" :class="{
    has-error:error}">
    <label :for="name">{{label}}</label>

    <input v-if="type=='text'" type="text" :id="name" :name="name" :value="value">

    <select v-if="type=='select'" :id="name" :name="name">

        <option v-for="option in options" :value="option" :selected="option==value">{{option}}</option>
    </select>

    <div v-if="error" class="error">{{error}}</div>
</div>


<style>
    root {
        margin-bottom: 1em;

    }

    .error {
        color: red;
    }

    input+.error {
        margin-top: -1em;
    }
</style>

<?php

$type = $props['type'] ?? "text";

// print_r($props['options']);
