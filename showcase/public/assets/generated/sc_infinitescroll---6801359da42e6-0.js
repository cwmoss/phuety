
    let elem = document.querySelector('.ifs-container');
    let infScroll = new InfiniteScroll(elem, {
        // options
        path: '/blog?page={{#}}',
        append: 'article',
        history: 'push',
        status: '.page-load-status'
    });
