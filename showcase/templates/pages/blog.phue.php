<app.layout title="Blog" :path="props.path">

    <h1>Blog</h1>

    <p>This is an example for using infinite scroll.<br>
        There are 10 pages of Lorem ipsum...</p>



    <sc.infinitescroll>

        <article :foreach="article in res.data">
            <header>
                <h2>{{article.title}}</h2>
            </header>
            <p>{{article.body}}</p>
        </article>

    </sc.infinitescroll>

    <sc.code file="pages/blog.vue.php"></sc.code>
    <sc.code file="components/sc_infinitescroll.vue.php"></sc.code>
</app.layout>


<style>
    h1 {
        color: gold;
    }
</style>

<?php

use Leaf\Fetch;

$page = $props->page ?? 1;
$res = Fetch::get("https://jsonplaceholder.typicode.com/posts?_limit=10&_page=" . $page);
// print_r($res);

// sleep(1);
?>