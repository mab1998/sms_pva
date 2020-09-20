<?php

return array(


    'pdf' => array(
        'enabled' => true,
        /*For Linux*/
         'binary'  => 'wkhtmltox/bin/wkhtmltopdf',
        /*For windows*/
//        'binary'  => '"C:\Program Files\wkhtmltopdf\bin\64bit\wkhtmltopdf.exe "',
        'timeout' => false,
        'options' => array(),
        'env'     => array(),
    ),
    'image' => array(
        'enabled' => true,
        'binary'  => '/usr/local/bin/wkhtmltoimage',
        'timeout' => false,
        'options' => array(),
        'env'     => array(),
    ),


);
