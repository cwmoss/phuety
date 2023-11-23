<div class="splide">
    <ul class="splide__pagination"></ul>
    <div class="splide__track">
        <div class="splide__list">
            <div v-for="person in res.data.results" class="slide splide__slide">
                <div>
                    <h4>{{person.name.first}} {{person.name.last}}</h4>
                    <img :src="person.picture.large"></img>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var splide = new Splide('.splide', {
            autoWidth: true,
            autoHeight: true,
        });
        splide.mount();
    });
</script>

<style>
    .slide {
        width: 200px;
    }
</style>
<?php

use Leaf\Fetch;

$res = Fetch::get("https://randomuser.me/api/?results=10");

?>