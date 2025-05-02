<?php

require_once("site.php");
// require_once("phuety_adapter.php");

use slowfoot\configuration;
use slowfoot\loader\dataset;
use slowfoot\store;
use slowfoot_plugin\markdown;
use slowfoot_plugin\phuety\phuety_adapter;

return new configuration(
  site_name: 'phuety Documentation',
  site_description: 'Docs for phuety',
  path_prefix: "/phuety",
  // store: "memory",
  sources: [
    "chapter" => new markdown\loader('content/**/*.md', remove_prefix: "content/"),
    'chapter_index' => site::load_chapter_index(...)
  ],
  templates: [
    'chapter' => '/:slug',
  ],
  plugins: [
    new site(),
    new markdown\markdown_plugin(),
  ],
  build: "/docs/dist",
  template_engine: phuety_adapter::class
);
