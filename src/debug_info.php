<?php

namespace phuety;

class debug_info {

    public ?string $src = null;
    public int $compile_offset = 0;
    public int $php_offset = 0;
    public int $source_line = 0;
    // runcode startline / debug info startline
    public array $run_code = [39, 15];
    public function __construct(public string $file, public int $error_line) {
    }

    public function get_message(): string {
        $this->fetch_info();
        if (!$this->src) return "";
        $source = $this->fetch_source();
        return sprintf("in component:\n%s:%s\n ==> %s", $this->src, $this->source_line, $source);
    }

    public function fetch_source(): string {
        $content = file($this->src);
        $offset = $this->error_line - $this->run_code[0] + 0; // ($this->compile_offset - $this->run_code[1])
        $this->source_line = $this->php_offset + $offset;

        return $content[$this->source_line] ?? "";
    }

    public function fetch_info() {
        $content = file_get_contents($this->file);
        $debug_comment = array_find(token_get_all($content), fn($it) => $it[0] == T_DOC_COMMENT);
        if ($debug_comment) {
            $this->compile_offset = $debug_comment[2];
            $line = str_replace(["/**", "*/", "*"], "", $debug_comment[1]);
            $line = explode("~", trim($line));
            $this->src = trim($line[0]);
            $this->php_offset = (int) trim($line[1]);
        }
    }
}

/*

TODO;

PHP Fatal error:  Cannot use phuety as phuety because the name is already in use in /Users/rw/dev/playground/phuety/showcase/tmp/page_blog_component.php on line 15
Stack trace:
#0 /Users/rw/dev/playground/phuety/src/phuety.php(263): phuety\phuety->load_component_class('page_blog', '/Users/rw/dev/p...')
#1 /Users/rw/dev/playground/phuety/src/phuety.php(250): phuety\phuety->load_component('page_blog')
#2 /Users/rw/dev/playground/phuety/src/phuety.php(158): phuety\phuety->get_component('page.blog')
#3 /Users/rw/dev/playground/phuety/src/phuety.php(147): phuety\phuety->collect_all('page.blog', Array)
#4 /Users/rw/dev/playground/phuety/src/phuety.php(120): phuety\phuety->collect('page.blog')
#5 /Users/rw/dev/playground/phuety/showcase/showcase.php(69): phuety\phuety->run('page.blog', Array)
#6 /Users/rw/dev/playground/phuety/showcase/public/index.php(7): require('/Users/rw/dev/p...')

*/