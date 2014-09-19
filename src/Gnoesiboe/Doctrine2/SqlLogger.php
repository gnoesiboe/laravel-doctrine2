<?php

namespace Gnoesiboe\Doctrine2;

/**
 * Logger
 */
class SqlLogger implements \Doctrine\DBAL\Logging\SQLLogger
{
    /**
     * Logs a SQL statement somewhere.
     *
     * @param string $sql The SQL to be executed.
     * @param array|null $params The SQL parameters.
     * @param array|null $types The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $params = is_null($params) ? array() : $params;

        \Log::info(' --> [query] :: \'' . $sql . '\' [params] :: ' . $this->toString($params), array('query'));
    }

    /**
     * @param array $params
     * @return string
     */
    protected function toString(array $params)
    {
        $out = '';

        foreach ($params as $key => $value) {
            $out .= "$key : $value, ";
        }

        return $out;
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {
        //@todo??
    }
}
