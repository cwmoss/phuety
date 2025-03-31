    php 8.4

    composer install

    php -S localhost:4000 -t showcase/public/

## todo

- [ ] component for client-only processing
- [ ] attribute for client-only processing
- [ ] client-only :bind (::bind? -- alpine, vue, ...)
- [ ] dynamic component <component :is="input_type"></component>
- [ ] defered component (like <assets> via attribute)
- [ ] teleport? component or attribute?
- [x] assets: automatic write js to file (or leave embeded)
- [ ] assets: cache buster dev, cache buster prod
- [ ] compile to php-string-templates
- [ ] rewrite expression parser
- [ ] test with vue order of rendering

## inspiration, copypaste, similar projects

- https://github.com/wmde/php-vuejs-templating
- https://github.com/ctxcode/vue-pre
- https://github.com/php-templates/php-templates
- https://github.com/leongrdic/php-smplang
