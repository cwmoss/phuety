<app.layout title="About Us" :path="props.path">

    <h1>Our Team</h1>
    <sc.team :persons="res.data.results"></sc.team>

    <sc.code file="pages/about.phue.php"></sc.code>
    <sc.code file="components/sc_team.phue.php"></sc.code>
</app.layout>


<style>
    h1 {
        color: gold;
    }
</style>

<?php

# use Leaf\Fetch;

$res = $helper->fetch("https://randomuser.me/api/?results=12");

?>