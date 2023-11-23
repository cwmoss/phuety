<div>
    <div v-for="person in res.data.results" class="slide">
        <h4>{{person.name.first}} {{person.name.last}}</h4>
        <img :src="person.picture.large"></img>
    </div>
</div>


<?php

use Leaf\Fetch;

$res = Fetch::get("https://randomuser.me/api/?results=10");

?>