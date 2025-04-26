## start

    install php 8.4

    composer install

    php -S localhost:4000 -t showcase/public/

## what?

phuety are vue inspired component/dom based templates run by php

## syntax

components have a dot in it's name.

the name is all lowercase. it must start with a letter and can contain numbers. it must contain at least one dot (.). don't use dashes as they are reserved for web components.

### :if, ph-if

### :else, ph-else

### :foreach, ph-foreach

    offer in basket.offers
    offer, key in basket.offers
    basket.offers as offer
    basket.offers as offer, key
    basket.offers as key => offer

### :html, ph-html

### :[name], ph-bind:[name]

### :class

:class binding is merged with class attribute

### <template.></template.>

for wrapping multiple elements with v-if/v-else/v-for

### <slot.>, <slot.[name]></slot.[name]>, :slot, ph-slot

The <slot.\*> element is a slot outlet that indicates where the parent-provided slot content should be rendered.

If you need multiple slot outlets in a single component, you can use named slots.

`<slot.>` is a shorthand for `<slot.default>`

slots can have default content, that is rendered, if not provided by the calling template.

    <footer><slot.footer><em>this is the end</em></slot.footer></footer>

To pass slotted content to a component, use the slot directive.

    <my.card>
        <span :slot="footer">updated: {{recent_update}}</span>
        <h1>updates for axel</h1>
    </my.card>

Elements without a slot directive are passed as the default slot. Only direct childs of a component can be passed as named slots.

### <app.assets head|body />

links to css/js files

## single file components (sfc)

single file components can contain template code, script code, style code and php code (must be the very last section).

## component map

it needs a prefix based map to find the components in your project.

    $map = [
        // <app.layout> => layout.vue.php
        'app.layout' => 'layout',
        // <app.assets> => assets.vue.php
        'app.assets' => 'assets',
        'phuety-*' => '*',
        // page.contact => pages/contact.vue.php
        'page.*' => 'pages/*',
        // form.field => form/form_field.vue.php
        'form.*' => 'form/',
        // sc.qrcode => components/sc_qrcode.vue.php
        'sc.*' => 'components/'
    ];

## api

    $phuety = new phuety\phuety(
        // base dir for sfc sources
        __DIR__ . '/templates',
        // components map
        $map,
        // directory for compiled templates
        __DIR__ . '/tmp'
    );

## examples

look into `showcase/` dir

## todo

- [ ] resolve paths at compile time
- [ ] component for client-only processing?
- [ ] attribute for client-only processing?
- [ ] client-only :bind (::bind? -- alpine, vue, ...)?
- [ ] dynamic component <component :is="input_type"></component>
- [ ] defered component (like <assets> via attribute)
- [ ] teleport? component or attribute?
- [x] assets: automatic write js to file (or leave embeded)
- [ ] assets: cache buster dev, cache buster prod
- [x] compile to php-string-templates
- [x] new expression parser => take symfony for now
- [ ] test with vue order of rendering

## inspiration, copypaste, similar projects

- https://github.com/wmde/php-vuejs-templating
- https://github.com/ctxcode/vue-pre
- https://github.com/php-templates/php-templates
- https://github.com/leongrdic/php-smplang
