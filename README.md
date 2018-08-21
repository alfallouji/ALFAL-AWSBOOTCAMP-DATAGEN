# Disclaimer

<b>This code is provided free of charge. If you decide to deploy this on an EC2 instance in AWS (using the cloudformation script) or generate data and send them to your AWS resources (e.g. to your Kinesis stream, Firehose, Dynamodb table, etc.), you may incur charges related to the resources you are using in AWS.</b>

# Data Generator

This tool provides an easy way to generate data sample and push them to the following targets : 
 - Kinesis Streams (AWS)
 - Kinesis Firehose (AWS)
 - Dynamodb (AWS)
 - SQS (AWS)
 - Cloudwatch logs (AWS)
 - S3 (AWS)
 - Lambda (AWS)
 - CSV file (locally)
 - JSON file (locally)

The tool comes with a web GUI and command line script. 

## Web GUI Screenshot
![Alt text](https://github.com/alfallouji/SCREENS/blob/master/Datagen/gui.png "Screenshot of the GUI")

## Features
You have full control on the structure of the data that you want to generate. The structure of the generated data can be defined within a configuration file. The following features are supported : 

 - Random integer (within a min-max range)
 - Random element from a list
 - Random element from a weighted list (e.g. 'elem1' => 20% of chance, 'elem2' => 40% of chance, etc.)
 - Constant
 - Timestamp / Date
 - Counter (increment & decrement)
 - Mathematical expression using previously defined fields 
     `(({field1} + {field2} / 4) * {field3})`
 - Conditionnal rules : 
    `{field3} equals TRUE if {field1} + {field2} < 1000`
    `{field4} equals FALSE if {field1} + {field2} >= 1000`
- Any of the feature exposed by `fzaninotto/faker` library
- String expression that includes any other pre-defined field
- Ability to defined the overall distribution (e.g I want 20% of my population to have a value of 'Y' for {field3}). The generator will run until it meets the desired distribution.
- Ability to hide a variable (if you only need it to compute another variable)
- Multi-dimensional array

You can also defined the the size of the population. The generated data will be pushed to a kinesis stream by batch (size of the batch is configurable). 

When defining rules that are used by a distribution, ensure that the rules make sense. The engine will keep generating data until it reaches the desired distribution. For example, if you define the following : 

    'field1' => array(
        'type' => 'rules',
        // Value => condition
        'rules' => array(
            'Y' => '{field2} + {field3} > 1060',
            'N' => '{field2} + {field3} <= 1060',
        ),        
    ),

And, then define the following distribution : 

    'distribution' => array(
        'disable' => false,
	'fields' => array(
	    'field1' => array(
                'Y' => 80,
                'N' => 20,
             ),
        ),
    )

`{field2} + {field3}` can never be equal or below 1060, then the generator will end up with an infinite loop. Since it will keep running until it reaches the desired distribution.

# Deployment 

You can either deploy the solution locally or deploy it in AWS. From a performance perspective, you will get a better speed (request/sec) by deploying it in AWS (better network latency for the Kinesis API calls).

## Keep in mind

- Deploying and executing the code on an ec2 instance will provide the best performance for AWS targets (low latency).
- Speed will also depend on how you have configured some of the service (e.g. number of shards that you have defined for Kinesis Streams).

## Deploy it on AWS

The easy way is to use the following link to deploy the latest version : [Build it on AWS](https://console.aws.amazon.com/cloudformation/home?region=us-east-1#/stacks/new?stackName=awsDatagenStack&templateURL=https://s3.amazonaws.com/alfal-awsbootcamp-datagen/cloudformation.json)

If you have customized the code (e.g. added custom config, profiles or implementation), you will need to go through the following steps to create a custom deployment package :

 1. Clone the repository (and modify it according to your needs)
 2. Create an S3 Bucket in AWS
 3. Install the AWS CLI 
 4. Run `sh setup/deploy.sh <YourS3BucketName>` (this will package the code and store it in S3)
 5. Run `sh setup/create-stack.sh` to create the cloudformation stack or use the console
 6. Wait for the cloudformation to finish (takes approx. 6 minutes). You will find the web URL in the output section of the cloudformation stack.

Notes :
- If you are using the console to create the cloudformation stack, then make sure to check the box saying : `"I acknowledge that AWS CloudFormation might create IAM resources."`
- The cloudformation script will install php7, apache and deploy the code on the ec2 instance. 
- Check the output to get the link of the web page. For example : http://ec2-34-22-91-12.compute-1.amazonaws.com/kinesis-datagen

## Deploy it locally

You can also (for testing purpose) deploy the solution locally, by doing the following : 

 1. Clone the repository
 2. Run `sh setup/composer.sh` to install composer (if you don't have it)
 3. Create a config/credentials.php file (use the sample as an example) with the right aws credentials
 4. You will need to install PHP7+ and apache (e.g. yum install php71 on macos, apt-get install php7.1 on Ubuntu)


# Usage

## Web console 
For the web console, open your browser and open index.php. 

## Command line
You can also use the command line script generate.php. For usage help, run `php generate.php --help`.


# Annex

## Example of a data structure

    // Define the desired distribution (optional)
    'distribution' => array(
        // This allows to switch off the distribution
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

        // Do not display this field by setting the optional parameter hide to true
        // This field is just used to compute the value of another field
        'field9' => array(
            'type' => 'faker',
            'property' => 'country',
            'hide' => true
        ),

        // A string expression can include any pre-defined field (even the hidden ones)
        'stringExpression' => array(
            'type' = 'stringExpression',
            'stringExpression' => 'IP:{field8} and field5={field5} and country={field9}'
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
