<?php


namespace Bones\Message\Driver\Mongo;


class QueryBuilder
{

    const OPERATOR_NOT_EQUAL = '$ne';
    const OPERATOR_OR = '$or';
    const OPERATOR_AND = '$and';
    const OPERATOR_IN = '$in';
    const OPERATOR_NOT_IN = '$nin';

    const ORDER_ASC = 1;
    const ORDER_DESC = -1;

    /**
     * @param $field
     * @param $value
     * @return array
     */
    public static function Equal($field, $value)
    {
        return array($field => $value);
    }

    /**
     * @param $field
     * @param $value
     * @return array
     */
    public static function NotEqual($field, $value)
    {
        return array ($field => array(self::OPERATOR_NOT_EQUAL => $value));
    }

    /**
     * @param $conditionList
     * @return array
     */
    public static function GetOr($conditionList)
    {
        return array(
            self::OPERATOR_OR => $conditionList
        );
    }

    /**
     * @param $conditionList
     * @return array
     */
    public static function GetAnd($conditionList)
    {
        return array(
            self::OPERATOR_AND => $conditionList
        );
    }

    public static function GetIn($field, $values)
    {
        return array($field => array(
            self::OPERATOR_IN => $values
        ));

    }

    public static function GetNotIn($field, $values)
    {
        return array($field => array(
            self::OPERATOR_NOT_IN => $values
        ));

    }
}
