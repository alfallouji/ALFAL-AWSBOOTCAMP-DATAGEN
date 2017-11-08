## Requirements

In order to run, make sure to have the following : 

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
