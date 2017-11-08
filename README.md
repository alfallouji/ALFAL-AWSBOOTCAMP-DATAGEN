## Kinesis Data Generator

This provides an easy way to generate data sample and push them to an AWS Kinesis Stream.

The structure of the generated data can be defined within a configuration file. 

The following features are supported : 

 - Random integer (within a min-max range)
 - Random element from a list
 - Random element from a weighted list (e.g. 'elem1' => 20% of chance, 'elem2' => 40% of chance, etc.)
 - Constant
 - Timestamp / Date
 - Mathematical expression using previously defined fields 
     `{{field1} + {field2} / 4) * {field3})`
 - Conditional rules : 
    `{field3} equals TRUE if {field1} + {field2} < 1000`
    `{field4} equals FALSE if {field1} + {field2} >= 1000`
- Any of the feature exposed by `fzaninotto/faker` library 
- Ability to defined the overall distribution (e.g I want 20% of my population to have a value of 'Y' for {field3}). The generator will run until it meets the desired distribution.

You can also defined the the size of the population. The generated data will be pushed to a kinesis stream by batch (size of the batch is configurable). 

When defining rules, it is your responsibility to ensure that the rules make sense. For example, if you defined the following : 

    'field1' => array(
		'type' => 'mathExpression',
		'mathExpression	' => '1000 + {field2}',
	)

And, then define the following distribution : 

	'distribution' => array(
        'field1' => array(
            '' => 80,
            'africa' => 20,
        ),
    )

Assuming that {field2} can only be a positive integer. You will end up with an infinite loop, since the generator will keep looping until it reaches the desired distribution.

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

### For development
If you want to run this locally (e.g. on your laptop), make sure to create a config/credentials.php file (use the sample as an example). Provide the adequate access key and secret. You will have to install PHP7+ and apache.

## Keep in mind

- Deploying and executing the code on an ec2 instance will provide the best performance (lower latency to push to Kinesis).
- Speed will also depend on the number of shards that you have defined for the kinesis stream.
