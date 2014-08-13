<?php
return array(
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOSqlite\Driver',
                'params' => array(
                    'path'=> ':memory:',
                )
            )
        )
    ),
    'facebook' => array(
        'fb_appid' => 'xxxxxx',
    )
);
