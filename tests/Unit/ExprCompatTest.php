<?php

use phuety\expression\parser;
use phuety\expression\evaluator;
use phuety\expression\data;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;

test('example', function () {
    expect(true)->toBeTrue();
});

test('number parsing', function () {
    $smpl = new parser;

    assertEquals($smpl->evaluate('1'), 1);
    assertEquals($smpl->evaluate('-1'), -1);
    assertEquals($smpl->evaluate('1.0'), 1.0);
    assertEquals($smpl->evaluate('-1.0'), -1.0);
    assertEquals($smpl->evaluate('12.34'), 12.34);
    assertEquals($smpl->evaluate('-12.34'), -12.34);
});

test('basic arithmetics', function () {
    $smpl = new parser;

    assertEquals($smpl->evaluate('1 + 2'), 3);
    assertEquals($smpl->evaluate('1 - 2'), -1);
    assertEquals($smpl->evaluate('2 * 3'), 6);
    assertEquals($smpl->evaluate('6 / 3'), 2);
    assertEquals($smpl->evaluate('13 % 5'), 3);
    assertEquals($smpl->evaluate('2 ** 3'), 8);

    assertEquals($smpl->evaluate('2 * 2 + 2'), 6);
    assertEquals($smpl->evaluate('2 * (2 + 2)'), 8);

    assertEquals($smpl->evaluate('4 / 2 - 1'), 1);
    assertEquals($smpl->evaluate('4 / (2 - 1)'), 4);

    $smpl = new parser(null, [
        'number' => 123,
    ]);

    assertEquals($smpl->evaluate('(number - 10 * 4 / 2 - 3) % 10'), 0);
    assertEquals($smpl->evaluate('(number-10*4/2-3)%10'), 0);

    $smpl = new parser(null, [
        'number' => '000123.0',
    ]);

    assertEquals($smpl->evaluate('(number - 10.0 * 04.0 / 2.0 - 3.0) % 10.0'), 0);
    assertEquals($smpl->evaluate('(number-10.0*04.0/2.0-3.0)%10.0'), 0);
});

test('arithmetics comparisons', function () {
    $smpl = new parser(null, [
        'number' => 123,
        'negative' => false,
    ]);

    assertTrue($smpl->evaluate('(100 + number * 1 <= 200 || number < -1) === negative'));
    assertTrue($smpl->evaluate('(100+number*1<=200||number<-1)===negative'));

    assertFalse($smpl->evaluate('0 > 1'));
    assertTrue($smpl->evaluate('0 < 1'));

    assertFalse($smpl->evaluate('1 < 1'));
    assertFalse($smpl->evaluate('1 > 1'));

    assertTrue($smpl->evaluate('1 <= 1'));
    assertTrue($smpl->evaluate('1 >= 1'));

    assertTrue($smpl->evaluate('0 <= 1'));
    assertTrue($smpl->evaluate('1 >= 0'));

    assertFalse($smpl->evaluate('1 <= 0'));
    assertFalse($smpl->evaluate('0 >= 1'));
});


test('boolean expressions', function () {
    $smpl = new parser();

    assertTrue($smpl->evaluate('true'));
    assertTrue($smpl->evaluate('!false'));
    assertFalse($smpl->evaluate('!true'));
    assertFalse($smpl->evaluate('false'));

    assertTrue($smpl->evaluate('true && false && false || true && true'));
    assertTrue($smpl->evaluate('!false && !true && false || !false && true'));
    assertTrue($smpl->evaluate('true&&false&&false||true&&true'));
    assertTrue($smpl->evaluate('((true)&&false&&false||true&&true)'));
    assertFalse($smpl->evaluate('!((true)&&false&&false||true&&true)'));

    assertFalse($smpl->evaluate('true && false && (false || true) && true'));
    assertFalse($smpl->evaluate('!false && !true && (false || !false) && true'));
    assertFalse($smpl->evaluate('true&&false&&(false||true)&&true'));
    assertFalse($smpl->evaluate('((true)&&false&&(false||true)&&true)'));
    assertTrue($smpl->evaluate('!((true)&&false&&(false||true)&&true)'));
});



test('array and object definitions', function () {
    $smpl = new parser(null, [
        'key' => 'third'
    ]);

    assertEquals($smpl->evaluate('[]'), []);
    # TODO! trailing , 
    #assertEquals($smpl->evaluate('[ "one", "two", 23, ]'), ["one", "two", 23]);
    ##assertEquals($smpl->evaluate('["first": "one", "second": "two", key: 23]'), ["first" => "one", "second" => "two", "third" => 23]);
    #assertEquals($smpl->evaluate('["array": ["foo", `bar`]]'), ["array" => ["foo", "bar"]]);
    #assertEquals($smpl->evaluate('["array": {\'foo\', "bar",},]'), ["array" => (object) ["foo", "bar"]]);

    #assertEquals($smpl->evaluate('{}'), (object) []);
    #assertEquals($smpl->evaluate('{ "one", "two", 23, }'), (object) ["one", "two", 23]);
    assertEquals($smpl->evaluate('{first: "one", second: "two", key: 23}'), (object) ["first" => "one", "second" => "two", "key" => 23]);
    #assertEquals($smpl->evaluate('{array: ["foo", `bar`]}'), (object) ["array" => ["foo", "bar"]]);
    #assertEquals($smpl->evaluate('{array: {\'foo\', "bar",},}'), (object) ["array" => (object) ["foo", "bar"]]);
});
