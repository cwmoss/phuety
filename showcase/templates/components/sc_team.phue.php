<section class="splide">
    <ul class="splide__pagination"></ul>
    <div class="splide__track">
        <div class="splide__list">
            <aside v-foreach="person in props.persons" class="slide splide__slide">

                <h3>{{person.name.first}} {{person.name.last}}</h3>
                <img :src="person.picture.large"></img>

            </aside>
        </div>
    </div>
</section>

<script head src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">

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
        margin-right: 16px;

    }
</style>