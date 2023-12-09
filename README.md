    php 8.1, 8.2

    composer install

    php -S localhost:4000 -t showcase/public/

## todo

o component for client-only processing
o attribute for client-only processing
o client-only :bind (::bind? -- alpine, vue, ...)
o dynamic component <component :is="input_type"></component>

o defered component (like <assets> via attribute)
o teleport? component or attribute?
x assets: automatic write js to file (or leave embeded)

o assets: cache buster dev, cache buster prod
o compile to php-string-templates
o rewrite expression parser

## inspiration, copypaste, similar projects

- https://github.com/wmde/php-vuejs-templating
- https://github.com/ctxcode/vue-pre
- https://github.com/php-templates/php-templates
