<nav>

    <a v-if="!subnav" href="/"><sc-logo></sc-logo></a>
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
$subnav = $props['subnav'] ?? false;

$top = [
    ['url' => '/', 'title' => 'Home'],
    ['url' => '/about', 'title' => 'About Us'],
    ['url' => '/forms', 'title' => 'Forms']
];

$sub = ['forms' => [
    ['url' => '/demo-form', 'title' => 'Basic Form'],
    ['url' => '/demo-webco', 'title' => 'Form with Webcomponent'],
    ['url' => '/demo-alpine', 'title' => 'Form with alpinejs']
]];

if ($subnav) {
    $navpoints = $sub[$subnav];
} else {
    $navpoints = $top;
}
