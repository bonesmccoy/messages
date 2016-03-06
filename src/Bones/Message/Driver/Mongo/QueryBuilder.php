<?php


namespace src\Bones\Message\Driver\Mongo;


class QueryBuilder
{
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
        return array ('$ne' => self::Equal($field, $value));
    }

    /**
     * @param $conditionList
     * @return array
     */
    public static function GetOr($conditionList)
    {
        return array(
            '$or' => $conditionList
        );
    }

    /**
     * @param $conditionList
     * @return array
     */
    public static function GetAnd($conditionList)
    {
        return array(
            '$and' => $conditionList
        );
    }

    public static function GetIn($field, $values)
    {
        return array($field => array(
            '$in' => $values
        ));

    }

    public static function GetNotIn($field, $values)
    {
        return array($field => array(
            '$nin' => $values
        ));

    }
}
