<?php

error_reporting(E_ALL);

class tokenstream {
    public array $data;
    public int $index = -1;
    public int $maxindex = 0;
    public function __construct($code) {
        // remove spaces & php start tag
        $this->data =  array_values(array_filter(PhpToken::tokenize('<?php ' . $code), fn ($tok) => !in_array($tok->id, [392, 389])));
        $this->maxindex = (count($this->data) - 1);
    }

    public function next() {
        $this->index++;
        return $this->data[$this->index] ?? null;
    }
    public function peek() {
        return $this->data[$this->index + 1] ?? null;
    }

    public function foreward() {
        return [
            $this->data[$this->index] ?? null,
            $this->next(),
            $this->peek()
        ];
    }

    public function more() {
        return $this->index < $this->maxindex;
    }
}

class leaf {
    public string $type = "";
    public string $value = "";

    public function __construct($type, $value = null) {
        $this->type = $type;
        if (!is_null($value)) $this->value = $value;
    }
    public function value($tok) {
        $this->type = 'var';
        $this->value .= $tok->text;
    }
}

class node {
    public string $type = "";
    public string $operator = "";
    public string $value = "";
    public $left = null;
    public $right = null;

    public function value($tok) {
        $this->type = 'var';
        $this->value .= $tok->text;
    }
    public function operator($tok) {
        $this->operator = $tok->text;
    }
    public function leaf($leaf) {
        if (!$this->left) {
            $this->left = $leaf;
        } else {
            $this->right = $leaf;
        }
    }
    public function xleaf($type) {
        $leaf = new leaf($type);
        if (!$this->left) {
            $this->left = $leaf;
        } else {
            $this->right = $leaf;
        }
        return $leaf;
    }
}

$test = "req.method == 'GET'";
$test = "req.method == 'GET' && date < now || has_feature || is_good || is_notbad";
// $test = "has_feature == true";
// $test = "5+ 13 & 4^2+1";
$stream = new tokenstream($test);
print_r($stream);


$prec = [
    '==' => 10,
    '<' => 10,
    '&&' => 2,
    '||' => 1
];

$tree = parse($stream, 0, $prec);
print_r($tree);
parse(null, 0, $prec);
print to_php($tree);

/*

    req.method == 'GET' && date < now || has_feature

    n request.method
    if == (10) < 0   N
    op == 
    rval 10 => n 'GET'
               if && (2) < 10 Y => ret 'GET' 
    [== request.method 'GET']
    *
    if && (2) < 0 N
    op &&
    rval 2 => n date
              if < (10) < 2 N
              op <
              rval 10 => n now
                        if || (1) < 10 Y => ret now
              [< date now]
              *
              if || (1) < 2 Y => [< date now]
    [&& [== request.method 'GET'] [< date now]]
    *
    if || (1) < 0 N
    op ||
    rval 1 => n has_feature
              no more token => has_feature
    [|| [&& [== request.method 'GET'] [< date now]] has_feature]
    * 
    no more token
    [|| [&& [== request.method 'GET'] [< date now]] has_feature]

    Array
(
    [0] => ||
    [1] => Array
        (
            [0] => &&
            [1] => Array
                (
                    [0] => ==
                    [1] => req.method
                    [2] => 'GET'
                )

            [2] => Array
                (
                    [0] => <
                    [1] => date
                    [2] => now
                )

        )

    [2] => has_feature
)

*/
/*
https://stackoverflow.com/questions/42610626/is-it-necessary-to-convert-infix-notation-to-postfix-when-creating-an-expression
*/
function parse($stream, $minprec, $prec) {
    static $count = 0;
    if ($stream == null) {
        print "final count $count\n";
        return;
    }
    $count++;
    $left = $stream->next();
    $node = $left->text;
    while ($stream->more()) {

        $peek = $stream->peek();

        if ($left->id == 262) {
            while (is_var($peek, $left->text)) {
                [$prev, $left, $peek] = $stream->foreward();
                $node .= ($left->text);
            }
        }

        $op_prec = $prec[$peek->text];
        if ($op_prec < $minprec) {
            break;
        }

        $op = $stream->next()->text;
        $rval = parse($stream, $op_prec, $prec);
        $node = [$op, $node, $rval];
    }
    print "$count\n";
    return $node;
}

function to_php($tree) {
    [$op, $lft, $rgt] = $tree;
    if (is_array($lft)) {
        $lft = to_php($lft);
    }
    if (is_array($rgt)) {
        $rgt = '(' . to_php($rgt) . ')';
    }
    return $lft . ' ' . $op . ' ' . $rgt;
}

function is_var($next, $current = null) {
    if ($current != '.' && $next && $next->text == '.') return true;
    if ($current == '.' && $next->id == 262) return true;
    return false;
}

function is_compare($token) {
    return in_array($token->id, [366, 60]);
}

function is_logic($token) {
    return in_array($token->id, [365]);
}

function parse0($stream, $root, $current = null) {
    while ($token = $stream->next()) {
        if ($token->id == 389) continue;

        $peek = $stream->peek();

        if ($token->id == 262) {
            if (!$current) {
                $current = new node;
            }
            $leaf = new leaf('var');
            $leaf->value($token);
            while (is_var($peek, $token->text)) {
                [$prev, $token, $peek] = $stream->foreward();
                $leaf->value($token);
            }

            $current->leaf($leaf);
            #return $result;
            continue;
        }

        if (is_logic($token)) {
            if ($current && !$current->type) {
            }
            $node = new node();
            $node->type = 'logic';
            $node->operator($token);
            $node->left = $root;
            return parse($stream, $node);
        }

        if (is_compare($token)) {
            if ($current && !$current->type) {
                $current->operator($token);
                $current->type = 'compare';
                continue;
            }

            $node = new node();
            $node->operator($token);
            $node->type = 'compare';
            $node->left = $root;
            return parse($stream, $root, $node);
            continue;
        }

        if ($token->id == 269) {
            $current->leaf(new leaf('literal', $token->text));
            continue;
        }
        $root->leaf($current);
    }
    return $root;
}
