<layout title="About Us" :path="props.path">

    <h1>Our Team</h1>
    <sc-team :persons="res.data.results"></sc-team>

</layout>

<style>
    h1 {
        color: gold;
    }
</style>

<?php



use Leaf\Fetch;

$res = Fetch::get("https://randomuser.me/api/?results=12");

?>