<?php module("Database Driver Base Class");

interface IDatabaseDriver {
    function connect();
    function disconnect();
    function escapeString($args);
    function query($sql);
}

abstract class DatabaseDriver implements IDatabaseDriver { }

