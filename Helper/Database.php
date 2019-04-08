<?php
/**
 * source: https://phpdelusions.net/pdo/pdo_wrapper - Thanks ;-)
 * 
 * If you want to write your own PDO wrapper, don't do it.
 * Read these articles instead:
 *
 * https://phpdelusions.net/pdo/common_mistakes
 * https://phpdelusions.net/pdo/pdo_wrapper
 * https://phpdelusions.net/pdo (the long answer)
 *
 */

namespace Tables\Helper;

class Database extends \PDO
{
    public function __construct($dsn, $username = NULL, $password = NULL, $options = [])
    {
        $default_options = [
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ];
        $options = array_replace($default_options, $options);
        parent::__construct($dsn, $username, $password, $options);
    }
    public function run($sql, $args = NULL)
    {
        if (!$args)
        {
             return $this->query($sql);
        }
        $stmt = $this->prepare($sql);
        $stmt->execute($args);
        return $stmt;
    }
}
