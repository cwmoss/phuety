<nav>

    <a :if="!subnav" href="/"><sc-logo></sc-logo></a>
    <ul>
        <li :foreach="item in navpoints">
            <a :href="item.url" :class="active==item.url?'active':''" :html="item.title"></a>
        </li>
    </ul>

</nav>

{{phuety}}

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

$active = $props->path ?? '/';
$subnav = $props->subnav ?? false;

$top = [
    (object)['url' => '/', 'title' => 'Home'],
    (object)['url' => '/blog', 'title' => 'Blog'],
    (object)['url' => '/about', 'title' => 'About Us'],
    (object)['url' => '/forms', 'title' => 'Forms']
];

$sub = ['forms' => [
    (object)['url' => '/demo-form', 'title' => 'Basic Form'],
    (object)['url' => '/demo-webco', 'title' => 'Form with Webcomponent'],
    (object)['url' => '/demo-alpine', 'title' => 'Form with alpinejs']
]];

if ($subnav) {
    $navpoints = $sub[$subnav];
} else {
    $navpoints = $top;
}
