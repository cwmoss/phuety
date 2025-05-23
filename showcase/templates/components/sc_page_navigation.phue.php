<nav :aria-label="label??'Pagination Navigation'" role="navigation">
    <a :foreach="range(1, total_pages) as p" :href="update_url(p)"
        :class="{active:current_page==p}" :html="p"></a>
</nav>

<style>
    root {
        display: flex;
        justify-content: start;
    }

    a {
        text-decoration: none;
        padding: .25rem;
        margin: .25rem;
    }

    a.active {
        background-color: black;
        color: white;
    }
</style>

<?php
$query = $_GET;
$my_url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$current_page = $query["page"] ?? 1;
$total_pages = $props->total_pages ?? 1;
$update_url = fn($page) =>  $my_url . '?' . http_build_query(["page" => $page] + $query);
