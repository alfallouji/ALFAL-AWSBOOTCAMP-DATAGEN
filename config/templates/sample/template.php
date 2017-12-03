<?php

return array(

    // Define the desired distribution (optional)
    'distribution' => array(
        'disable' => false,
        'fields' => array(
            // We want to have 30% of our distribution with a value of 'Y' for the result field
            // and 70% with a value of 'N'
            'result' => array(
                'Y' => 3,
                'N' => 7,
            ),
        ),
    ),

    // Define the desired fields (mandatory)
    'fields' => array(

        // You can use date function and provide the desired format
        'time' => array(
            'type' => 'date',
            'format' => 'Y-m-d H:i:s',
        ),

        // Randomly pick an integer number between 10 and 100
        'field1' => array(
            'type' => 'randomNumber',
            'randomNumber' => array(
                'max' => '100',
                'min' => '10',
            ),
        ),

        // Field2 is a constant equalts to 1000 (could be any string)
        'field2' => array(
            'type' => 'constant',
            'constant' => '1000',
        ),

        // Randomly pick an element from a defined list of values
        'field3' => array(
            'type' => 'randomList',
            'randomList' => array(
                'us',
                'europe',
                'asia',
            ),
        ),

        // Pick an element from a weighted list 
        'field4' => array(
            'type' => 'weightedList',
            'weightedList' => array(
                'men' => 40,
                'women' => 60,
            ),
        ),

        // You can use mathematical expression 
        'field5' => array(
            'type' => 'mathExpression',
            // Value => condition
            'mathExpression' => '{field1} + {field2} + sin({field2}) * 10',
        ),

        // You can use any of the faker feature
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

        // You can define conditonal rules to be evaluated in order to get the value
        // if this condition is true : {field1} + {field2} > 1060, then the value for {result} is 'Y'
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
