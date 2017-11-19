<?php

return array(
    
    'distribution' => array(
        'result' => array(
            'Won' => 51,
            'Lost' => 42,
            'DNF' => 7,
        ),
    ),

    'fields' => array(

        'decrement' => array(
            'type' => 'counter',
            'counter' => array(
                'start' => 1000,
                'step' => -1,
            )
        ),

        'datestamp' => array(
            'type' => 'date',
            'format' => 'Y-m-d H:i:s',
        ),

        'playerid' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '1000000000',
                'min' => '1000009999',
            ),
        ),

        'playerlevel' => array(
            'type' => 'weightedList',
            'weightedList' => array(
                'Level 1' => 5,
                'Level 2' => 10,
                'Level 3' => 12,
                'Level 4' => 5,
                'Level 5' => 3,
                'Level 6' => 7,
                'Level 7' => 13, 
                'Level 8' => 3, 
                'Level 9' => 15,
            ),
        ),

        'squadelementmap' => array(
            'type' => 'weightedList',
            'weightedList' => array(
                'Earth' => 20,
                'Water' => 30,
                'Fire' => 10,
                'Air' => 25,
                'Space' => 15,
            ),
        ),

        'gamenumber' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '1',
                'min' => '6',
            ),
        ),

        'squadpower' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '10',
                'min' => '14',
            ),
        ),

        'squadagility' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '10',
                'min' => '16',
            ),
        ),

        'squadhealth' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '8',
                'min' => '12',
            ),
        ),

        'squadluck' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '10',
                'min' => '20',
            ),
        ),

        'squadspecial' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '10',
                'min' => '18',
            ),
        ),

        'squadguard' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '10',
                'min' => '12',
            ),
        ),

        'squaddamage' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '10',
                'min' => '15',
            ),
        ),

        'squadinventoryitemcount' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '10',
                'min' => '15',
            ),
        ),

        'bosspower' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '40',
                'min' => '50',
            ),
        ),

        'bossagility' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '40',
                'min' => '50',
            ),
        ),
       
        'bosshealth' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '40',
                'min' => '50',
            ),
        ),

        'bossluck' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '40',
                'min' => '50',
            ),
        ),

        'bosspecial' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '40',
                'min' => '50',
            ),
        ),

        'bossguard' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '40',
                'min' => '50',
            ),
        ),

        'bossdamage' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '40',
                'min' => '70',
            ),
        ),

        // This field will be computer but not displayed by setting the optional hide parameter to true
        'counter' => array(
            'type' => 'counter',
            'counter' => array(
                'start' => 2,
                'step' => 5,
            ),
            'hide' => true,
        ),

        // A string expression can contain any other pre-defined field
        'stringExpression' => array(
            'type' => 'stringExpression',
            'stringExpression' => '{decrement} | {datestamp} - A constant value here - {nada},{counter}-{{bosspecial}}',
        ),
           
        'result' => array(
            'type' => 'rules',
            // Value => condition
            'rules' => array(
                'Won' => '{squadpower} + {squadluck} + {squadguard} + {squadagility} > {bossguard}',
                'Lost' => '{squadpower} + {squadluck} + {squadguard} + {squadagility} < {bossguard} - 3',
                'DNF' => '{squadpower} + {squadluck} + {squadguard} + {squadagility} <= {bossguard}',
            ),        
        ),
    ),
);
