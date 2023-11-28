<nav>

    <a href="/"><sc-logo></sc-logo></a>
    <ul>
        <li v-for="item in navpoints">
            <a :href="item.url" :class="active==item.url?'active':''" v-html="item.title"></a>
        </li>
    </ul>
</nav>

<style>
    a.active {
        text-decoration: none;
        color: black;
    }
</style>

<style global>
    h1 {
        color: magenta;
    }
</style>
<?php

$active = $props['path'] ?? '/';

$navpoints = [
    ['url' => '/', 'title' => 'Home'],
    ['url' => '/about', 'title' => 'About Us'],
    ['url' => '/contact', 'title' => 'Contact'],
    ['url' => '/contact2', 'title' => 'Contact (alpine)']
];


?>