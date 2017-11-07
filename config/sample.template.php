<?php

return array(
    
    'distribution' => array(
        'result' => array(
            'Y' => 3,
            'N' => 7,
        ),
    ),

    'fields' => array(
        'time' => array(
            'type' => 'date',
            'format' => 'Y-m-d H:i:s',
        ),

        'field1' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '100',
                'min' => '10',
            ),
        ),

        'field2' => array(
            'type' => 'constant',
            'constant' => '1000',
        ),

        'field3' => array(
            'type' => 'randomList',
            'randomList' => array(
                'us',
                'europe',
                'asia',
            ),
        ),

        'field4' => array(
            'type' => 'weightedList',
            'weightedList' => array(
                'men' => 40,
                'women' => 60,
            ),
        ),

        'field5' => array(
            'type' => 'mathExpression',
            // Value => condition
            'mathExpression' => '{field1} + {field2}',
        ),

        'field6' => array(
            'type' => 'faker',
            'property' => 'name',
        ),

        'field7' => array(
            'type' => 'faker',
            'property' => 'email',
        ),

        'field8' => array(
            'type' => 'faker',
            'property' => 'ipv4',
        ),

        'result' => array(
            'type' => 'rules',
            // Value => condition
            'rules' => array(
                'Y' => '{field1} + {field2} > 1060',
                'N' => '{field1} + {field2} <= 1060',
            ),        
        ),
    ),
);
