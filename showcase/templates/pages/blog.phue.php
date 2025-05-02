<app.layout title="Blog" :path="props.path">

    <h1>Blog</h1>

    <sc.code file="pages/blog.phue.php"></sc.code>
    <sc.code file="components/sc_infinitescroll.phue.php"></sc.code>

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


</app.layout>


<style>
    h1 {
        color: gold;
    }
</style>

<?php

$page = $props->page ?? 1;
$res = $helper->fetch("https://jsonplaceholder.typicode.com/posts?_limit=10&_page=" . $page);
// print_r($res);

// sleep(1);
?>