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

## How?

### Naming

All Components have a dot in it's name.

The name is all lowercase. It must start with a letter and can contain numbers. It must contain at least one dot (.). Don't use dashes as they are reserved for Web Components.

### Phuety Components are Single File Components (SFC)

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
If you have cases, where you don't want a template section at all, you can output html directly in the php section via `print()`, `echo` or `<?= "short echo tag" ?>`.

#### PHP Section

In the php section, you have access to the following variables:

- (object) `$props`, containing the properties passed to the component,
- (object) `$helper`, containing optional helper functions, passed to the phuety engine
- (array) `$slots`, containing the slots passed to the component.

#### Template Section

In the template section, you have access to the following variables:

- (object) `$props`, containing the properties passed to the component
- all variables and closures defined in the php section

The properties are also merged with the defined variables in this order (first wins):

1. `$props` (the property object)
2. local defined names
3. property names

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

The styles section above is transformed to scoped styles. You can disable scoping using the attribute `global`.
The special selector `root` is for addressing all template root elements (in this case the `<nav>` element).

### :if, ph-if

If on the same Element the `:if` directive get processed before the :foreach directive.

### :else, ph-else

The :else directive must directly follow an `:if` or `:elseif` directive.

### :elseif, ph-elseif

The `:elseif` directive must directly follow an `:if` or another `:elseif` directive.

```html
<div :if="hour<11">Morning!</div>
<div :elseif="hour < 17">Good Afternoon!</div>
<div :else>Good Evening</div>
```

### :foreach, ph-foreach

If on the same Element the `:if` directive get processed before the :foreach directive.

Expressions look like this:

```
// php style
basket.offers as key => offer
basket.offers as offer
basket.offers as offer, key
// js style
offer in basket.offers
offer, key in basket.offers

// example
<li :foreach="basket.offers as offer" :html="offer.title"></li>
```

### :html, ph-html

Contents of `:html` are inserted as plain HTML.

### :[name], ph-bind:[name]

Attributes can be a rendered by an expression. This is done via the bind directive. We need to differentiate between attributes of phuety components and attributes of html elements.

#### Component Attributes

Attributs of components are passed as properties via the `$props` object to the components. Dashes in attribute names are converted to underscores (kebab-case to snake_case). Binded Properties can be objects, arrays, strings, etc.

`:class` and `:style` bindings are special. see below.

`class`, `style`, `id` attributes on components are fallthrough attributes. see below.

```php
<!-- first.name.phue.php -->
<h1>{{ title }}</h1>
<div :html="person_list[0].name"></div>

<!-- some.calling.page.phue.php -->
<first.name title="some title" :person-list="names"></first.name>

<?php
$names = [
    (object) ["name"=>"Anna"]
];
```

#### Attributes of Html Elements

Attributes of html elements can only be strings. However, If you bind an object or array to an attribute, it will automatically serialized as JSON. This can be useful,
if you are using Web Components or other Javascripts and you need some initial state. Attribute names are not converted on Html Elements.

Known boolean Attributes will be omitted, if the expression returns a falsy value.

`:class` and `:style` bindings are special. see below.

```php
<dialog :open="show_dialog">
<!-- <dialog open> -->
    <names-list title="some title" :person-list="names"></names-list>
<!-- <names-list title="some title" person-list="[{&quot;name&quot;:&quot;Anna&quot;}]"></names-list> -->
</dialog>

<?php
$show_dialog = true;
$names = [
    (object) ["name"=>"Anna"]
];
```

### :class

`:class` binding is merged with class attribute

    <div :class="temperature" class="dishes"></div>
    ["temperature" => "cold"] =>  <div class="dishes cold"></div>

When binding is an object, it's keys are toggled as class names based on their truthiness.

    <div :class="{hot: high_temperature, cold: !high_temperature}" class="dishes"></div>
    ["high_temperature" => true] =>  <div class="dishes hot"></div>

When binding is an array, it's values are added to the class names.

    <div class="dishes" :class="multi"></div>
    ["multi" => ["one", "two"] =>  <div class="dishes one two"></div>

### :style

`:style` binding is merged with style attribute

    <div :style="'font-size: small'" style="font-size: big"></div>
    => <div style="font-size: big; font-size: small"></div>

When binding is an object, it's key names are converted from camelCase to kebab-case.

    <div :style="{fontSize: 'small', backgroundColor: 'red'}" style="font-size: big">
    => <div style="font-size: big; font-size: small; background-color: red;"></div>

### Fallthrough Attributes

When a component renders a single root element,
fallthrough attributes will be automatically added to the root element's attributes.

Fallthrough attributes are: `id`, `class` and `style`.

```html
<my.button class="large"></my.button>

<!-- template of <my.button> -->
<button>Click Me</button>

<!-- result -->
<button class="large">Click Me</button>
```

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

### run(), render(), render_template_string()

    // print output to stdout
    $phuety->run($component_name, ['the' => 'variables', 'go' => 'here']);

    // render output
    $output = $phuety->render($component_name, ['the' => 'variables', 'go' => 'here']);

    // render template string
    $output = $phuety->render_template_string("<h1 :html="title"></h1>", ['title' => 'Hello']);

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
