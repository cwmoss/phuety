## Start

    git clone https://github.com/cwmoss/phuety
    cd phuety
    composer install
    php -S localhost:4000 -t showcase/public/

> [!NOTE]
> This project is still beta. The API is subject to change.

## What?

_phuety_ is a vue inspired dom/component based template engine, run by the fabulous php 🐘.

_phuety_ gives you a nice way to code the html views in your application.

- familiar syntax, if you know vue :white_check_mark:
- you can use plain php in your templates :white_check_mark:
- automatic, context aware escaping :white_check_mark:
- it's fast, since it compiles to php :white_check_mark:

## Syntax

All Components have a dot in it's name.

The name is all lowercase. It must start with a letter and can contain numbers. It must contain at least one dot (.). Don't use dashes as they are reserved for Web Components.

### Single File Components (SFC)

Single File Components can contain template code, script code, style code and php code (php code must be the very last section).

#### Example

```php title="page_navigation.phue.php"
<!-- page_navigation.phue.php -->
<nav :aria-label="label??'Pagination Navigation'">
    <a :foreach="range(1, total_pages) as p" :href="update_url(p)"
        :class="{active:current_page==p}" :html="p"></a>
</nav>

<style>
    root {
        display: flex;
        justify-content: start;
    }

    a {
        text-decoration: none;
        padding: .25rem;
        margin: .25rem;
    }

    a.active {
        background-color: black;
        color: white;
    }
</style>

<?php
$query = $_GET;
$my_url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$current_page = $query["page"] ?? 1;
$total_pages = $props->total_pages ?? 1;
$update_url = fn($page) =>  $my_url . '?' . http_build_query(["page" => $page] + $query);
```

You can now use your new pagination component like this:

```html
<page.navigation total_pages="7" current_page="3"></page.navigation>
```

This example above contains a template section (comment and nav tag), a style section and a php section. Every section is optional.
If you have cases, where you don't want a template section, you can output html directly in the php section via `print()`, `echo` or `<?= "short echo tag" ?>`.

In the php section, you have access to the following variables:

- (object) `$props`, containing the properties passed to the component,
- (object) `$helper`, containing optional helper functions, passed to the phuety engine
- (array) `$slots`, containing the slots passed to the component.

In the template secion, you have access to the following variables:

- (object) `$props`, containing the properties passed to the component
- all variables and closures defined in the php section

The properties are also merged with the defined variables in this order (first wins):  
"props" (the property object) => local defined names => property names

```html
<div :html="props.name"></div>
<!-- <div>Joe</div> -->
<div :html="name"></div>
<!-- <div>Joe</div> -->
```

```html
<div :html="props.name"></div>
<!-- <div>Joe</div> -->
<div :html="name"></div>
<!-- <div>Anna</div> -->
<?php
$name = "Anna"
```

The styles section above is transformed to scoped styles. You can disable scoping using the attribute `global`. The special selector `root` is for addressing all template root elements.

### :if, ph-if

### :else, ph-else

### :elseif, ph-elseif

```html
<div :if="hour<11">Morning!</div>
<div :elseif="hour < 17">Good Afternoon!</div>
<div :else>Good Evening</div>
```

### :foreach, ph-foreach

    offer in basket.offers
    offer, key in basket.offers
    basket.offers as offer
    basket.offers as offer, key
    basket.offers as key => offer

### :html, ph-html

Contents of `:html` are inserted as plain HTML.

### :[name], ph-bind:[name]

### :class

:class binding is merged with class attribute

### <template.>

The template tag is for wrapping multiple elements with :if, :else, :elseif, :foreach.

### {{ expression }}

Use the "Mustache" syntax (double curly braces) to place contents in text areas.

    <span>Message: {{ msg }}</span>

### <slot.>, <slot.[name]>, :slot, ph-slot

The `<slot.[name]>` element is a slot outlet that indicates where the parent-provided slot content should be rendered.

If you need multiple slot outlets in a single component, you can use named slots.

`<slot.>` is a shorthand for `<slot.default>`

slots can have default content, that is rendered, if not provided by the calling template.

    <footer><slot.footer><em>this is the end</em></slot.footer></footer>

To pass slotted content to a component, use the slot directive.

    <my.card>
        <span :slot="footer">last update: {{updated_at}}</span>
        <h1>updates for axel</h1>
        <!-- more content here -->
    </my.card>

Elements without a slot directive are passed as the default slot. Only direct childs of a component can be passed as named slots.

### Expressions

_phuety_ uses the Symfony ExpressionLanguage component. It uses a specific syntax which is based on the expression syntax of _Twig_.

Some examples:

- foo ?? 'no'
- foo.baz ?? foo['baz'] ?? 'no'
- fruit?.getStock()
- 'hello ' ~ name

https://symfony.com/doc/current/reference/formats/expression_language.html

### <app.assets head|body />

links to css/js files

## Component Map

It needs a prefix based map to find the components in your project.
If you provide an empty map, that means, that all components must be
full prefixed in template source folder.

    $map = [
        // <app.layout> => layout.phue.php
        'app.layout' => 'layout',
        // <app.assets> => assets.phue.php
        'app.assets' => 'assets',
        // page.contact => pages/contact.phue.php
        'page.*' => 'pages/*',
        // form.field => form/form_field.phue.php
        'form.*' => 'form/',
        // sc.qrcode => components/sc_qrcode.phue.php
        'sc.*' => 'components/'
    ];

## API

    $phuety = new phuety\phuety(
        // base dir for sfc sources
        __DIR__ . '/templates',
        // components map
        $map,
        // directory for compiled templates
        __DIR__ . '/tmp'
    );

## Examples

look into `showcase/` dir

## TODO (maybe)

- [ ] resolve paths at compile time?
- [ ] client-only processing with phuety-skip and phuety-long-attributes
- [ ] dynamic component <component :is="input_type"></component>?
- [ ] defered component (like <assets> via attribute)?
- [ ] teleport? component or attribute?
- [x] assets: automatic write js to file (or leave embeded)
- [ ] assets: cache buster dev, cache buster prod
- [x] compile to php-string-templates
- [x] new expression parser => take symfony for now
- [ ] test with vue order of rendering

## Inspiration, copypaste, similar projects

- https://github.com/wmde/php-vuejs-templating
- https://github.com/ctxcode/vue-pre
- https://github.com/php-templates/php-templates
- https://github.com/leongrdic/php-smplang
- https://github.com/tempestphp/tempest-framework/tree/main/src/Tempest/View
