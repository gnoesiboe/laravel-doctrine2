<?php

return array(

    /*
     * Contains a list of connections
     */
    'connections' => array(

        /*
         * One individual connection, in this case the default connection. If no specific connection is requested, this
         * connection is returned.
         */
        'default' => array(
            'driver'    => 'pdo_mysql',
            'user'		=> 'root',
            'password'	=> '',
            'dbname'	=> '',
            'host'		=> 'localhost',
            'prefix'	=> ''
        )
    ),

    'paths' => array(
        'entity' => array(

            /*
             * Folder or folders containing the entity mapping (mapping between entities en their database equivalents)
             */
            'mapping' => array(
                app_path('entity' . DIRECTORY_SEPARATOR . 'mapping'),
            )
        ),

        /*
         * Folder used to save the proxy objects in. Proxy objects are relations that have not been retrieved yet. The objects
         * work as a proxy so that, when the object is asked for, the data is retrieved from the database.
         */
        'proxy' => sys_get_temp_dir()
    ),


    /*
     * Configuration for doctrine migrations
     */

    'migrations' => array(

        /*
         * ?? //@todo check what is done with this config
         */
        'name' => 'Doctrine migrations',

        /*
         * PHP Namespace for migration files
         */
        'namespace' => 'DoctrineMigrations',

        /*
         * Name of the table that holds the application's version state
         */
        'table_name' => 'doctrine_migration_versions',

        /*
         * Diretory that the generated migration files are send to
         */
        'directory' => app_path('database' . DIRECTORY_SEPARATOR . 'migrations'),
    )
);
