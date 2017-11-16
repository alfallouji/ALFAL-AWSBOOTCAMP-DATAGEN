## Disclaimer

<b>This code is provided free of charge. If you decide to deploy this on AWS (using the cloudformation script), you may incur charges related to the resources you are using in AWS (e.g. EC2, S3, Kinesis, etc.).</b>

## Data Generator

This tool provides an easy way to generate data sample and push them to something like a AWS Kinesis stream.

The structure of the generated data can be defined within a configuration file. 

The following features are supported : 

 - Random integer (within a min-max range)
 - Random element from a list
 - Random element from a weighted list (e.g. 'elem1' => 20% of chance, 'elem2' => 40% of chance, etc.)
 - Constant
 - Timestamp / Date
 - Counter (increment & decrement)
 - Mathematical expression using previously defined fields 
     `{{field1} + {field2} / 4) * {field3})`
 - Conditionnal rules : 
    `{field3} equals TRUE if {field1} + {field2} < 1000`
    `{field4} equals FALSE if {field1} + {field2} >= 1000`
- Any of the feature exposed by `fzaninotto/faker` library 
- Ability to defined the overall distribution (e.g I want 20% of my population to have a value of 'Y' for {field3}). The generator will run until it meets the desired distribution.

You can also defined the the size of the population. The generated data will be pushed to a kinesis stream by batch (size of the batch is configurable). 

When defining rules, it is your responsibility to ensure that the rules make sense. For example, if you defined the following : 

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
        'field1' => array(
            'Y' => 80,
            'N' => 20,
        ),
    )

If `{field2} + {field3}` can never be equal or below 1060, then the generator will end up with an infinite loop. Since it will keep running until it reaches the desired distribution.

## Deploy it on AWS

You can either deploy the solution locally or deploy it in AWS. From a performance perspective, you will get a better speed (request/sec) by deploying it in AWS (better network latency for the Kinesis API calls).

In order to deploy the solution to AWS, do the following : 

 1. Clone the repository
 2. Create an S3 Bucket in AWS
 3. Install the AWS CLI
 4. Run `sh setup/deploy.sh <YourS3BucketName>`
 5. Run `sh setup/create-stack.sh` to create the cloudformation stack
 6. Wait for the cloudformation to finish (takes approx. 6 minutes). You will find the web URL in the output section of the cloudformation stack.

You can also use the following link to deploy the latest version : [Build it on AWS](https://console.aws.amazon.com/cloudformation/home?region=us-east-1#/stacks/new?stackName=OpsAutomator&templateURL=https://s3.amazonaws.com/alfal-awsbootcamp-datagen/cloudformation.json)

## Deploy it locally

You can also (for testing purpose) deploy the solution locally, by doing the following : 

 1. Clone the repository
 2. Run `sh setup/composer.sh` to install composer (if you don't have it)
 3. Create a config/credentials.php file (use the sample as an example) with the right aws credentials
 4. You will need to install PHP7+ and apache

## Requirements

In order to run, make sure to have the following : 

- Run `composer install` after pulling the code from github
- Create an S3 bucket
 Update the bucketName in the deploy.sh script
- Use the deploy.sh script to deploy the code into S3 - this will also copy the cloudformation.json file there  
- Provide the adequate S3 Bucket name when creating the cloudformation stack
- Create a Kinesis Stream (remember its name, it will be needed in the webconsole)

## Usage

- Create a new cloudformation stack using the cloudformation.json file (which should be in the s3 bucket that you created previously). 
- If you are using the console to create the cloudformation stack, then make sure to check the box saying : `"I acknowledge that AWS CloudFormation might create IAM resources."`
- The cloudformation script will install php7, apache and deploy the code on the ec2 instance. 
- Check the output to get the link of the web page. For example : http://ec2-34-22-91-12.compute-1.amazonaws.com/kinesis-datagen

### Web console 
For the web console, use index.php.

### Command line
You can also use the command line script generate.php.

## Keep in mind

- Deploying and executing the code on an ec2 instance will provide the best performance (lower latency to push to Kinesis).
- Speed will also depend on the number of shards that you have defined for the kinesis stream.

## Example

    // Define the desired distribution (optional)
    'distribution' => array(
        // We want to have 30% of our distribution with a value of 'Y' for the result field
        // and 70% with a value of 'N'
        'result' => array(
            'Y' => 3,
            'N' => 7,
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
