<div class="ifs-container">
    <slot.></slot.>
</div>

<div class="page-load-status">
    <div class="loader-ellips infinite-scroll-request">
        <span class="loader"></span>
    </div>
    <p class="infinite-scroll-last">End of content</p>
    <p class="infinite-scroll-error">No more pages to load</p>
</div>



<script head src="https://unpkg.com/infinite-scroll@4/dist/infinite-scroll.pkgd.min.js"></script>

<script>
    let elem = document.querySelector('.ifs-container');
    let infScroll = new InfiniteScroll(elem, {
        // options
        path: '/blog?page={{#}}',
        append: 'article',
        history: 'push',
        status: '.page-load-status'
    });
</script>

<style>
    .page-load-status {
        display: none;
        /* hidden by default */
        padding-top: 20px;
        border-top: 1px solid #DDD;
        text-align: center;
        color: #777;
    }
</style>