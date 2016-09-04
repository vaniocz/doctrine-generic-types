<?php
namespace Vanio\DoctrineGenericTypes\Patches\DBAL\Schema;

use Doctrine\DBAL\SQLParserUtils;

class SQLParserUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providePlaceholderPositions
     * @param string $query
     * @param bool $isPositional
     * @param array $placeholderPositions
     */
    function test_getting_placeholder_positions(string $query, bool $isPositional, array $placeholderPositions)
    {
        $this->assertEquals($placeholderPositions, SQLParserUtils::getPlaceholderPositions($query, $isPositional));
    }

    public function providePlaceholderPositions(): array
    {
        return [
            // None
            ['SELECT * FROM foo', true, []],
            ['SELECT * FROM foo', false, []],

            // Positional parameters
            ['SELECT ?', true, [7]],
            ['SELECT * FROM foo WHERE bar IN (?, ?, ?)', true, [32, 35, 38]],
            ['SELECT ? FROM ?', true, [7, 14]],
            ['SELECT "?" FROM foo', true, []],
            ["SELECT '?' FROM foo", true, []],
            ['SELECT `?` FROM foo', true, []], // Ticket DBAL-552
            ['SELECT [?] FROM foo', true, []],
            ["SELECT 'Doctrine\DBAL?' FROM foo", true, []], // Ticket DBAL-558
            ['SELECT "Doctrine\DBAL?" FROM foo', true, []], // Ticket DBAL-558
            ['SELECT `Doctrine\DBAL?` FROM foo', true, []], // Ticket DBAL-558
            ['SELECT [Doctrine\DBAL?] FROM foo', true, []], // Ticket DBAL-558
            ['SELECT "?" FROM foo WHERE bar = ?', true, [32]],
            ["SELECT '?' FROM foo WHERE bar = ?", true, [32]],
            ['SELECT `?` FROM foo WHERE bar = ?', true, [32]], // Ticket DBAL-552
            ['SELECT [?] FROM foo WHERE bar = ?', true, [32]],
            ['SELECT * FROM foo WHERE jsonb_exists_any(foo.bar, ARRAY[?])', true, [56]], // Ticket GH-2295
            ["SELECT 'Doctrine\DBAL?' FROM foo WHERE bar = ?", true, [45]], // Ticket DBAL-558
            ['SELECT "Doctrine\DBAL?" FROM foo WHERE bar = ?', true, [45]], // Ticket DBAL-558
            ['SELECT `Doctrine\DBAL?` FROM foo WHERE bar = ?', true, [45]], // Ticket DBAL-558
            ['SELECT [Doctrine\DBAL?] FROM foo WHERE bar = ?', true, [45]], // Ticket DBAL-558
            ["SELECT * FROM foo WHERE bar = 'it\\'s a trap? \\\\' OR bar = ?\nAND baz = \"\\\"quote\\\" me on it? \\\\\" OR baz = ?", true, [58, 104]],
            ['SELECT * FROM foo WHERE foo = ? AND bar = ?', true, [1 => 42, 0 => 30]], // explicit keys

            // Named parameters
            ['SELECT :foo FROM :bar', false, [7 => 'foo', 17 => 'bar']],
            ['SELECT * FROM foo WHERE bar IN (:foo, :bar)', false, [32 => 'foo', 38 => 'bar']],
            ['SELECT ":foo" FROM foo WHERE bar IN (:bar, :baz)', false, [37 => 'bar', 43 => 'baz']],
            ["SELECT ':foo' FROM foo WHERE bar IN (:bar, :baz)", false, [37 => 'bar', 43 => 'baz']],
            ['SELECT :foo_bar', false, [7 => 'foo_bar']], // Ticket DBAL-231
            ['SELECT @foo := 1', false, []], // Ticket DBAL-398
            ['SELECT @foo := 1 AS foo, :bar AS bar FROM :baz', false, [25 => 'bar', 42 => 'baz']], // Ticket DBAL-398
            ['SELECT * FROM foo WHERE bar > :bar AND baz > :baz', false, [30 => 'bar', 45 => 'baz']], // Ticket GH-113
            ['SELECT foo::date as date FROM foo WHERE bar > :bar AND baz > :baz', false, [46 => 'bar', 61 => 'baz']], // Ticket GH-259
            ['SELECT `f.bar:baz` FROM foo f WHERE `f.bar` > :bar', false, [46 => 'bar']], // Ticket DBAL-552
            ['SELECT [f.bar:baz] FROM foo f WHERE [f.bar] > :bar', false, [46 => 'bar']], // Ticket DBAL-552
            ['SELECT * FROM foo WHERE jsonb_exists_any(foo.bar, ARRAY[:foo])', false, [56 => 'foo']], // Ticket GH-2295
        ];
    }
}
