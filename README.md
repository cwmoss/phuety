    php 8.1

    composer install

    php -S localhost:4000 -t showcase/public/

## todo

- component for client-only processing
- attribute for client-only processing
- client-only :bind (::bind? -- alpine, vue, ...)
- dynamic component <component :is="input_type"></component>
- defered component (like <assets> via attribute)
- assets: automatic extend to file (or leave embeded)
- assets: cache buster dev, cache buster prod
- compile to php-string-templates
- rewrite expression parser

## inspiration, copypaste, similar projects

- https://github.com/wmde/php-vuejs-templating
- https://github.com/ctxcode/vue-pre
- https://github.com/php-templates/php-templates
